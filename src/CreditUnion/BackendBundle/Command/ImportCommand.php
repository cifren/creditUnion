<?php

namespace CreditUnion\BackendBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CreditUnion\BackendBundle\Entity\ImportFormat;
use CreditUnion\FrontendBundle\Entity\Client;

/**
 * CreditUnion\BackendBundle\Command\ImportCommand
 */
class ImportCommand extends ContainerAwareCommand
{

    protected $output;
    protected $em;
    protected $timestart;

    protected function configure()
    {
        $this
                ->setName('import:client')
                ->setDescription('Import data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->em = $this->getContainer()->get('doctrine')->getEntityManager();
        $branches = $this->getContainer()->get('doctrine')->getRepository('CreditUnionFrontendBundle:Branch')->findAll();

        $this->log('****** Start import ******');
        $this->log('');
        foreach ($branches as $branch) {
            $this->log('--------- Branch name : ' . $branch->getName() . ' ---------');
            if ($branch->getImportFormat()) {
                $this->importClient($branch->getImportFormat());
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
        if (!$importFormat->getEnabled()) {
            $this->log('--> This import is disabled');
            return true;
        }
        $this->startTimer();

        //delete all element for the branch selected
        $query = $this->em
                ->createQuery('DELETE CreditUnionFrontendBundle:Client c WHERE c.branch = :branch')
                ->setParameter('branch', $importFormat->getBranch()->getId());
        $query->execute();

        //look for last file updated
        if (!file_exists($importFormat->getFolder())) {
            $this->log('-->  Error : Folder ' . $importFormat->getFolder() . ' doesn\'t exist');
            return;
        }
        $files = glob($importFormat->getFolder() . "/*.{xls,xlsx,csv}", GLOB_BRACE);
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);

        //get modified file
        $latest_file = key($files);
        if (empty($latest_file)) {
            $this->log('--> no file found in folder : ' . $importFormat->getFolder());
            return true;
        }
        $this->log('--> File : ' . $latest_file);

        //adapt in function of type csv or xls
        if ($importFormat->getType() == 'csv') {
            $objReader = \PHPExcel_IOFactory::createReader(strtoupper($importFormat->getType()));
            if ($importFormat->getDelimiterCsv()) {
                $objReader->setDelimiter($importFormat->getDelimiterCsv());
            }
            $objPHPExcel = $objReader->load($latest_file);
        } else {
            $objPHPExcel = \PHPExcel_IOFactory::load($latest_file);
        }

        $this->log('--> Loaded in ' . $this->getTime() . ' sec');
        $worksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); // e.g. 10
        $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $highestColumnNumber = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $importFormatColumnNumber = count($importFormat->getMatchField());

        if ($importFormatColumnNumber != $highestColumnNumber) {
            $this->log('--> Error : Number of column in excel file doesn\'t match the import format created for this store, in file ' . $highestColumnNumber . ' columns, in import format ' . $importFormatColumnNumber . ' columns');
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
            $client->setBranch($importFormat->getBranch());
            $this->em->persist($client);
        }
        $this->em->flush();
        $this->log('--> Finished in ' . $this->getTime() . ' sec');
        $this->log("--> $row Rows added with success");
    }

    protected function startTimer()
    {
        $this->timestart = microtime(true);
    }

    protected function getTime()
    {
        return round(microtime(true) - $this->timestart, 2);
    }

    protected function handlerType($importFormat, $type, $value)
    {
        if ($type == 'date') {
            $value = \DateTime::createFromFormat($importFormat->getDateFormat(), $value);
            if($value == false){
                $value = null;
            }
        }
        return $value;
    }

    protected function log($message)
    {
        $this->output->writeln($message);
    }

}
