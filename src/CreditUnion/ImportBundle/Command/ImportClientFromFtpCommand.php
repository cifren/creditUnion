<?php

namespace CreditUnion\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use CreditUnion\BackendBundle\Entity\ImportFormat;
use CreditUnion\FrontendBundle\Entity\Client;

/**
 * CreditUnion\BackendBundle\Command\ImportClientFromFtpCommand
 */
class ImportClientFromFtpCommand extends ContainerAwareCommand
{

    protected $output;
    protected $em;
    protected $log;

    /**
     * start time value
     * @var datetime
     */
    protected $startTime;

    /**
     * end time value
     * @var datetime
     */
    protected $endTime;

    /**
     * diff between starttime and endtime
     * @var datetime
     */
    protected $diffTime;

    /**
     * setup debug mode for error_reporting on screen or catch by handler
     * @var bool
     */
    protected $debug;

    protected function configure()
    {
        $this
                ->setName('import:clientFromFtp')
                ->setDescription('Import client data from ftp, can export csv or xls files')
                ->addArgument('debug', InputArgument::OPTIONAL, 'debug ? display issues')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug = $input->getArgument('debug') == 'true'?true:false;
        $this->handleError();

        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $branches = $this->getContainer()->get('doctrine')->getRepository('CreditUnionFrontendBundle:Branch')->findAll();

        $this->log('****** Start import ******');
        $this->log('');
        foreach ($branches as $branch) {

            $this->log('--------- Branch name : ' . $branch->getName() . ' ---------');
            if ($branch->getImportFormat()) {
                $this->saveLogRunning($branch->getImportFormat());
                $this->importClient($branch->getImportFormat());
                $this->saveLog($branch->getImportFormat());
            } else {
                $this->log('No import');
            }
            $this->log('');
        }
        $this->log('****** End import ******');

        //$output->writeln($text);
    }

    protected function importClient(ImportFormat $importFormat)
    {
        try{
            $this->clearLog();
            $this->log(date('Y-m-d h:i:s'));
            if (!$importFormat->getEnabled()) {
                $this->log('--> This import is disabled');
                return true;
            }
            $this->setStartTime();

            //delete all element for the branch selected
            $query = $this->em
                    ->createQuery('DELETE CreditUnionFrontendBundle:Client c WHERE c.branch = :branch')
                    ->setParameter('branch', $importFormat->getBranch()->getId());
            $query->execute();

            //reload branch because clear()
            $branch = $this->em->getRepository('CreditUnionFrontendBundle:Branch')->find($importFormat->getBranch()->getId());

            //look for last file updated
            if (!file_exists($importFormat->getFolder())) {
                $this->log('-->  Error : Folder ' . $importFormat->getFolder() . ' doesn\'t exist');
                return;
            }

            $extensionDateFormat = 'Ymd-His';
            $extensionInProgress = 'inProcess';
            $this->cleanFolder($importFormat->getFolder(), $extensionInProgress, $extensionDateFormat);

            $files = glob($importFormat->getFolder() . "/*.{xls,xlsx,csv}", GLOB_BRACE);
            $files = array_combine($files, array_map("filemtime", $files));
            arsort($files);

            //get modified file
            $latestFile = key($files);
            if (empty($latestFile)) {
                $this->log('--> no file found in folder : ' . $importFormat->getFolder());
                return;
            }
            $idFile = uniqid();
            $this->log('--> File : ' . $latestFile);

            //process the file, need to change name in case of other script launched at the same time
            $today = new \DateTime('now');
            $inProcessFileName = "{$latestFile}.{$idFile}.{$today->format($extensionDateFormat)}.{$extensionInProgress}";
            rename($latestFile,$inProcessFileName);

            //adapt in function of type csv or xls
            if ($importFormat->getType() == 'csv') {
                $objReader = \PHPExcel_IOFactory::createReader(strtoupper($importFormat->getType()));
                if ($importFormat->getDelimiterCsv()) {
                    $objReader->setDelimiter($importFormat->getDelimiterCsv());
                }
                $objPHPExcel = $objReader->load($inProcessFileName);
            } else {
                $objPHPExcel = \PHPExcel_IOFactory::load($inProcessFileName);
            }

            $this->log('--> Loaded in ' . $this->getFinishTime());
            $this->setStartTime();
            $worksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnNumber = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $importFormatColumnNumber = count($importFormat->getMatchField());

            if ($importFormatColumnNumber != $highestColumnNumber) {
                $this->log('--> Error : Number of column in file doesn\'t match the import format created for this store, in file ' . $highestColumnNumber . ' columns, in import format ' . $importFormatColumnNumber . ' columns');
                return false;
            }

            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= $highestRow; ++$row) {
                $client = new Client();
                //if title in file, it will escape from import
                if ($importFormat->getTitleDisplayed() && $row == 1) {
                    continue;
                }
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    //$dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
                    $colImport = $importFormat->getMatchField()[$col];
                    $type = $this->em->getClassMetadata('CreditUnionFrontendBundle:Client')->fieldMappings[$colImport]['type'];
                    $val = $this->handlerType($importFormat, $type, $val);

                    $client->set($colImport, $val);
                }
                $client->setBranch($branch);
                $this->em->persist($client);
                if($row%100 == 0){
                    $this->em->flush();
                    $this->em->clear();
                    //renew object for em
                    $branch = $this->em->getRepository('CreditUnionFrontendBundle:Branch')->find($branch->getId());
                }
            }
            $this->em->flush();
            $this->em->clear();
            $row--;
            $this->log('--> Finished in ' . $this->getFinishTime());
            $this->log("--> $row Rows added with success");

            //create archive folder
            $archiveFolder = $importFormat->getFolder().'/archive';
            $this->createArchiveFolder($archiveFolder );

            //archive folder
            $pathParts = pathinfo($latestFile);
            $archiveFileName = "{$archiveFolder}/{$pathParts['filename']}_{$today->format($extensionDateFormat)}.{$pathParts['extension']}";
            rename($inProcessFileName, $archiveFileName);
        }
        catch(Exception $e){
            $this->log(' --> Error : '.$e->getMessage());
        }
    }

    /**
     * setup start time to now
     *
     * @param type $id allow multiple timer in command line
     */
    protected function setStartTime()
    {
        $this->startTime = new \DateTime("now");
    }

    /**
     * setup end time to now
     *
     * @param type $id allow multiple timer in command line
     */
    protected function setEndTime()
    {
        $this->endTime= new \DateTime("now");
    }

    /**
     * setup diff between startTime and endTime
     *
     * @param type $id allow multiple timer in command line
     */
    protected function setFinishTime()
    {
        $this->diffTime = $this->startTime->diff($this->endTime);
    }

    /**
     * display diff time if verbose is true
     *
     * @param type $id allow multiple timer in command line
     */
    protected function getFinishTime()
    {
        $this->setEndTime();
        $this->setFinishTime();
        return $this->diffTime->format('%h Hours %i Minutes %s Seconds');
    }

    protected function handlerType($importFormat, $type, $value)
    {
        $typeDate = array('date', 'datetime');
        if (in_array($type, $typeDate)) {
            $value = \DateTime::createFromFormat($importFormat->getDateFormat(), $value);
            if ($value == false) {
                $value = null;
            }
        }
        return $value;
    }

    protected function log($message)
    {
        $this->output->writeln($message);
        $this->log .= '<br>' . $message;
    }

    protected function clearLog()
    {
        $this->log = null;
    }

    protected function saveLog($importFormat)
    {
        $importFormat = $this->em->getRepository('CreditUnionBackendBundle:ImportFormat')->find($importFormat->getId());
        $importFormat->setLog($this->log);
        $this->em->flush();
    }

    protected function saveLogRunning($importFormat)
    {
        $importFormat = $this->em->getRepository('CreditUnionBackendBundle:ImportFormat')->find($importFormat->getId());
        $importFormat->setLog('Script running...');
        $this->em->flush();
    }

    /**
     * delete all files with extension .inProcess where date in filename is not today
     */
    protected function cleanFolder($folder, $extensionInProgress, $extensionDateFormat)
    {
        $files = glob($folder . "/*.{$extensionInProgress}", GLOB_BRACE);
        foreach($files as $file){
            $pathParts = pathinfo($file);
            $explodedFileName = explode('.', $pathParts['basename']);
            $explodedFileName = array_reverse($explodedFileName);

            $dateFile = date_create_from_format($extensionDateFormat, $explodedFileName[1]);

            if($dateFile->format('Y-m-d') != date('Y-m-d')){
                system("rm $file");
            }
        }
    }

    protected function createArchiveFolder($archiveFolder)
    {
        if (!file_exists($archiveFolder)) {
            mkdir("$archiveFolder");
        }
    }

    /**
     * declare error handler in command
     *
     * Need to handle issue because command from symfony2 doesnt use listener, only for http request
     *
     */
    protected function handleError()
    {
        if (!$this->debug) {
            var_dump('plop');
            //catch fatal error
            register_shutdown_function(array($this, 'handleShutdown'));

            //overide error to communicate with database
            set_error_handler(array($this, "exception_error_handler"));
        }
    }

    /**
     * handle shutdown error from php and report them to the database
     */
    public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        $e = new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        $this->log(' --> Error : '.$e->getMessage());

        return true;
    }

    /**
     * handle shutdown error from php and report them to the database
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== NULL) {
            //fatal = type 1
            if ($error['type'] == 1) {
                $e = new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
                $this->log(' --> Error : '.$e->getMessage());
            }
        }
    }
}
