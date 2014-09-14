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
  protected $fininstitut;

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

  /**
   * verbosity of the command
   */
  protected $verbosity;

  protected function configure()
  {
    $this
            ->setName('import:clientFromFtp')
            ->setDescription('Import client data from ftp, can export csv or xls files')
            ->addArgument('fininstitut', InputArgument::REQUIRED, 'which fininstitut?')
            ->addArgument('debug', InputArgument::OPTIONAL, 'debug ? display issues')
            ->addArgument('debugLimit', InputArgument::OPTIONAL, 'debugLimit ? how many rows')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    ini_set("memory_limit", -1);

    $this->verbosity = $output->getVerbosity();
    $this->debug = $input->getArgument('debug') == 'true' ? true : false;
    $this->debugLimit = $input->getArgument('debugLimit');

    $this->fininstitut = preg_match('/^\d+$/', $input->getArgument('fininstitut')) ? $input->getArgument('fininstitut') : false;

    $this->output = $output;
    $this->em = $this->getContainer()->get('doctrine')->getManager();
    $repoFininstitut = $this->getContainer()->get('doctrine')->getRepository('CreditUnionFrontendBundle:Fininstitut');
    if ($this->fininstitut) {
      $fininstitutes = $repoFininstitut->findBy(array('id' => $this->fininstitut));
    } else {
      $fininstitutes = $repoFininstitut->findAll();
    }

    $this->log('****** Start import ******');
    $this->log('');
    foreach ($fininstitutes as $fininstitut) {

      $this->log('--------- Financial institution name : ' . $fininstitut->getName() . ' ---------');
      if ($fininstitut->getImportFormat()) {
        $date = date('Y-m-d H:i:s');
        if ($fininstitut->getImportFormat()->getEnabled()) {
          $this->log("Script running at {$date}...", $fininstitut->getImportFormat());

          $this->saveLog($fininstitut->getImportFormat());
          $this->clearLog();
          $this->importClient($fininstitut->getImportFormat());
        } else {
          $this->log("{$date} Script disabled", $fininstitut->getImportFormat());
        }

        $this->saveLog($fininstitut->getImportFormat());
        $this->clearLog();
      } else {
        $this->log('No import');
      }

      $this->log('');
    }
    $this->log('****** End import ******');
  }

  protected function importClient(ImportFormat $importFormat)
  {
    try {

      $this->setStartTime();

      //reload fininstitut because clear()
      $fininstitut = $this->em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($importFormat->getFininstitut()->getId());

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
        $this->log('--> Error : Number of column in file doesn\'t match the import format created for this fininstitut, in file ' . $highestColumnNumber . ' columns, in import format ' . $importFormatColumnNumber . ' columns', $importFormat);

        //archive folder
        $this->renameProcessToArchive($latestFile, $inProcessFileName, $importFormat);
        return false;
      }

      //delete list of client from the same fininstitut and replace by new one
      $this->deleteClient($importFormat);

      $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
      if ($highestColumnIndex != $importFormatColumnNumber) {
        $this->log("WARNING : The file columns count is: $highestColumnIndex, the exported columns count is $importFormatColumnNumber");
      }

      //rows
      for ($row = 1; $row <= $highestRow; ++$row) {
        $client = new Client();
        //if title in file, it will escape from import
        if ($importFormat->getTitleDisplayed() && $row == 1) {
          continue;
        }

        //columns
        for ($col = 0; $col < $importFormatColumnNumber; ++$col) {
          $cell = $worksheet->getCellByColumnAndRow($col, $row);
          $val = $cell->getValue();
          $colImport = $importFormat->getMatchField()[$col];

          $this->logDebug("Column: $colImport, value from file: $val, coordinate: {$cell->getCoordinate()}");
          $val = $this->handlerType($importFormat, $this->em->getClassMetadata('CreditUnionFrontendBundle:Client')->fieldMappings[$colImport], $cell);

          if ($colImport == 'birthDate') {
            $val = $this->handleBirthDate($val, $importFormat->getDateFormat());
          }

          try {
            $debugValue = var_export($val, true);
            $this->logDebug("Final value: $debugValue");
          } catch (\Exception $e) {
            $debugValue = var_export($val, true);
            $this->log("Final value: $debugValue");
          }
          $client->set($colImport, $val);
        }

        $client->setFininstitut($fininstitut);
        $this->em->persist($client);

        if ($this->debugLimit == $row) {
          break;
        }

        if ($this->debug) {
          $this->em->flush();
        }
        if ($row % 100 == 0) {
          if (!$this->debug) {
            $this->em->flush();
          }
          $this->em->clear();
          //renew object for em
          $fininstitut = $this->em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($fininstitut->getId());
        }
      }
      $this->em->flush();
      $this->em->clear();
      $row--;
      $this->log('--> Finished in ' . $this->getFinishTime(), $importFormat);
      $this->log("--> $row Rows added with success", $importFormat);

      //archive folder
      $this->renameProcessToArchive($latestFile, $inProcessFileName, $importFormat);
    } catch (\Exception $e) {
      $this->getContainer()->get('doctrine')->resetEntityManager();
      $this->em = $this->getContainer()->get('doctrine')->getManager();
      if ($this->debug) {
        $this->log($e->getMessage());
      }
      $this->log("Error Fatal, something looks wrong", $importFormat);
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
    //delete all element for the fininstitut selected
    $query = $this->em
            ->createQuery('DELETE CreditUnionFrontendBundle:Client c WHERE c.fininstitut = :fininstitut')
            ->setParameter('fininstitut', $importFormat->getFininstitut()->getId());
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

  protected function handleBirthDate($val, $format)
  {
    $dateToday = new \DateTime(date("Y-m-d"));
    if (!$val) {
      return $val;
    }
    if (strpos($format, 'y') !== false && $val->format('Ymd') >= $dateToday->format('Ymd')) {
      $val->sub(new \DateInterval('P100Y'));
    }
    return $val;
  }

  protected function handlerType($importFormat, $mapping, \PHPExcel_Cell $cell)
  {
    $value = $cell->getValue();
    $typeDate = array('date', 'datetime');
    if (in_array($mapping['type'], $typeDate)) {
      $value = \DateTime::createFromFormat($importFormat->getDateFormat(), $cell->getValue());
      if ($value == false) {
        $value = null;
      }
      //if $value is still null, means maybe date has special format in excel
      if ($value == null && \PHPExcel_Shared_Date::isDateTime($cell)) {
        $value = \PHPExcel_Shared_Date::ExcelToPHPObject($cell->getValue());
      }
    } elseif ($mapping['type'] == 'string') {
      if (isset($mapping['length'])) {
        $value = substr($cell->getFormattedValue(), 0, $mapping['length'] - 1);
      }
    }
    return $value;
  }

  protected function logDebug($message)
  {
    if ($this->verbosity == OutputInterface::VERBOSITY_VERY_VERBOSE) {
      $this->output->writeln($message);
    }
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
      $archiveFileName = array();
      if ($hours > 24) {
        //renaming
        $archiveFileName['extension'] = $explodedFileName[2];
        $archiveFileName['date'] = $explodedFileName[1];
        $archiveFileName['filename'] = null;
        for ($i = 3; $i <= count($explodedFileName) - 1; $i++) {
          $archiveFileName['filename'] .= $explodedFileName[$i];
        }
        $fileName = $archiveFileName['filename'] . ".error." . $archiveFileName['date'] . '.' . $archiveFileName['extension'];
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

  protected function getLogger()
  {
    return $this->getContainer()->get('logger');
  }

}
