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
class ImportClientFromFtpCommand extends ContainerAwareCommand {

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

    /**
     * archive folder name
     * @var string 
     */
    protected $archiveFolder = 'archive';

    /**
     * date format for extension
     */
    protected $extensionDateFormat = 'Ymd-His';

    /**
     * progress extension in file name
     */
    protected $extensionInProgress = 'inProcess';

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
        $this->debug = $input->getArgument('debug') == 'true' ? true : false;
        $this->handleError();

        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $branches = $this->getContainer()->get('doctrine')->getRepository('CreditUnionFrontendBundle:Branch')->findAll();

        $this->log('****** Start import ******');
        $this->log('');
        foreach ($branches as $branch) {

            $this->log('--------- Branch name : ' . $branch->getName() . ' ---------');
            if ($branch->getImportFormat()) {
                $this->log('Script running...', $branch->getImportFormat());
                $this->saveLog($branch->getImportFormat());
                $this->clearLog();
                $this->importClient($branch->getImportFormat());
                $this->saveLog($branch->getImportFormat());
                $this->clearLog();
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
        try {

            if (!$importFormat->getEnabled()) {
                $this->log(date('Y-m-d h:i:s'));
                $this->log('--> This import is disabled');
                return true;
            } else {
                $this->log(date('Y-m-d h:i:s'), $importFormat);
            }
            $this->setStartTime();

            //reload branch because clear()
            $branch = $this->em->getRepository('CreditUnionFrontendBundle:Branch')->find($importFormat->getBranch()->getId());

            //look for last file updated
            if (!file_exists($importFormat->getFolder())) {
                $this->log('-->  Error : Folder ' . $importFormat->getFolder() . ' doesn\'t exist', $importFormat);
                return;
            }

            $this->extensionDateFormat = 'Ymd-His';
            $this->extensionInProgress = 'inProcess';
            $this->cleanFolder($importFormat->getFolder());

            $files = glob($importFormat->getFolder() . "/*.{xls,xlsx,csv}", GLOB_BRACE);
            $files = array_combine($files, array_map("filemtime", $files));
            arsort($files);

            //get modified file
            $latestFile = key($files);
            if (empty($latestFile)) {
                $this->log('--> no file found in folder : ' . $importFormat->getFolder(), $importFormat);
                return;
            }
            $idFile = uniqid();
            $this->log('--> File : ' . $latestFile, $importFormat);

            //process the file, need to change name in case of other script launched at the same time
            $today = new \DateTime('now');
            $inProcessFileName = "{$latestFile}.{$idFile}.{$today->format($this->extensionDateFormat)}.{$this->extensionInProgress}";
            rename($latestFile, $inProcessFileName);

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

            $this->log('--> Loaded in ' . $this->getFinishTime(), $importFormat);
            $this->setStartTime();
            $worksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnNumber = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $importFormatColumnNumber = count($importFormat->getMatchField());

            if ($importFormatColumnNumber > $highestColumnNumber) {
                $this->log('--> Error : Number of column in file doesn\'t match the import format created for this branch, in file ' . $highestColumnNumber . ' columns, in import format ' . $importFormatColumnNumber . ' columns', $importFormat);

                //archive folder
                $this->renameProcessToArchive($latestFile, $inProcessFileName, $importFormat);
                return false;
            }

            //delete list of client from the same branch and replace by new one
            $this->deleteClient($importFormat);

            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= $highestRow; ++$row) {
                $client = new Client();
                //if title in file, it will escape from import
                if ($importFormat->getTitleDisplayed() && $row == 1) {
                    continue;
                }
                for ($col = 0; $col < $importFormatColumnNumber; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();
                    //$dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
                    $colImport = $importFormat->getMatchField()[$col];
                    $type = $this->em->getClassMetadata('CreditUnionFrontendBundle:Client')->fieldMappings[$colImport]['type'];
                    $val = $this->handlerType($importFormat, $this->em->getClassMetadata('CreditUnionFrontendBundle:Client')->fieldMappings[$colImport], $val);

                    $client->set($colImport, $val);
                }
                $client->setBranch($branch);
                $this->em->persist($client);
                if ($this->debug) {
                    $this->em->flush();
                }
                if ($row % 100 == 0) {
                    if (!$this->debug) {
                        $this->em->flush();
                    }
                    $this->em->clear();
                    //renew object for em
                    $branch = $this->em->getRepository('CreditUnionFrontendBundle:Branch')->find($branch->getId());
                }
            }
            $this->em->flush();
            $this->em->clear();
            $row--;
            $this->log('--> Finished in ' . $this->getFinishTime(), $importFormat);
            $this->log("--> $row Rows added with success", $importFormat);

            //archive folder
            $this->renameProcessToArchive($latestFile, $inProcessFileName, $importFormat);
        } catch (Exception $e) {
            $this->logError($e, $importFormat);
        }
    }

    protected function renameProcessToArchive($file, $process, $importFormat)
    {
        //create archive folder
        $archiveFolder = $importFormat->getFolder() . DIRECTORY_SEPARATOR . $this->archiveFolder;
        $this->createArchiveFolder($archiveFolder);

        $pathParts = pathinfo($file);
        $today = new \DateTime('now');
        $archiveFileName = "{$archiveFolder}/{$pathParts['filename']}_{$today->format($this->extensionDateFormat)}.{$pathParts['extension']}";
        rename($process, $archiveFileName);
    }

    protected function deleteClient($importFormat)
    {
        //delete all element for the branch selected
        $query = $this->em
                ->createQuery('DELETE CreditUnionFrontendBundle:Client c WHERE c.branch = :branch')
                ->setParameter('branch', $importFormat->getBranch()->getId());
        $query->execute();
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
        $this->endTime = new \DateTime("now");
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

    protected function handlerType($importFormat, $mapping, $value)
    {
        $typeDate = array('date', 'datetime');
        if (in_array($mapping['type'], $typeDate)) {
            $value = \DateTime::createFromFormat($importFormat->getDateFormat(), $value);
            if ($value == false) {
                $value = null;
            }
        } elseif ($mapping['type'] == 'string') {
            if (isset($mapping['length'])) {
                $value = substr($value, 0, $mapping['length'] - 1);
            }
        }
        return $value;
    }

    protected function log($message, $importFormat = null)
    {
        $this->output->writeln($message);
        if ($importFormat) {
            $this->log .= '<br>' . $message;
        }
    }

    protected function saveLog($importFormat)
    {
        $log = $this->log . $importFormat->getLog();
        $log = $this->limitLog($log);
        $importFormat = $this->em->getRepository('CreditUnionBackendBundle:ImportFormat')->find($importFormat->getId());
        $importFormat->setLog($log);
        $this->em->flush();
    }

    protected function clearLog()
    {
        $this->log = null;
    }

    protected function limitLog($log)
    {
        $log = substr($log, 0, 1500);

        return $log;
    }

    /**
     * Archive all files with extension .inProcess where date in filename is more than 24h
     */
    protected function cleanFolder($folder)
    {
        $files = glob($folder . "/*.{$this->extensionInProgress}", GLOB_BRACE);
        foreach ($files as $file) {
            $pathParts = pathinfo($file);
            $explodedFileName = explode('.', $pathParts['basename']);
            $explodedFileName = array_reverse($explodedFileName);

            $dateFile = date_create_from_format($this->extensionDateFormat, $explodedFileName[1]);
            $dateToday = new \DateTime(date("Y-m-d H:i:s"));
            //file less than 24h
            $diff = $dateToday->diff($dateFile);

            $hours = $diff->h;
            $hours = $hours + ($diff->days * 24);
            if ($hours > 24) {
                //renaming
                $archiveFileName['ext'] = $explodedFileName[2];
                $archiveFileName['date'] = $explodedFileName[1];
                $archiveFileName['filename'] = null;
                for ($i = 3; $i <= count($explodedFileName) - 1; $i++) {
                    $archiveFileName['filename'] .= $explodedFileName[$i];
                }
                $fileName = $archiveFileName['filename'] . ".error." . $archiveFileName['date'] . '.' . $archiveFileName['ext'];
                $archiveFileName = $pathParts['dirname']
                        . DIRECTORY_SEPARATOR . $this->archiveFolder
                        . DIRECTORY_SEPARATOR . '' . $fileName;
                rename($file, $archiveFileName);
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
        $this->logError($e);

        return true;
    }

    protected function logError(\Exception $exception, $importFormat = null)
    {
        $this->log('--> Fatal Error : ' . $exception->getMessage()
                . ' in file ' . $exception->getFile()
                . ' line ' . $exception->getLine()
                , $importFormat);
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
                $this->logError($e);
            }
        }
    }

}
