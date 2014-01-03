<?php

namespace CreditUnion\BackendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\BackendBundle\Entity\ImportFormat
 * 
 * @ORM\Entity
 * @ORM\Table(name="import_format")
 */
class ImportFormat
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $enabled;

    /**
     * @ORM\Column(type="text")
     */
    protected $folder;

    /**
     * @ORM\Column(type="string")
     */
    protected $dateFormat;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    protected $delimiterCsv;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $titleDisplayed;

    /**
     * @ORM\Column(type="array")
     */
    protected $matchField;

    /**
     * @ORM\Column(type="text")
     */
    protected $log;

    /**
     * @ORM\OneToOne(targetEntity="CreditUnion\FrontendBundle\Entity\Branch", inversedBy="importFormat")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=false)
     */
    protected $branch;

    public function getId()
    {
        return $this->id;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getFolder()
    {
        return $this->folder;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDelimiterCsv()
    {
        return $this->delimiterCsv;
    }

    public function getTitleDisplayed()
    {
        return $this->titleDisplayed;
    }

    public function getMatchField()
    {
        return $this->matchField;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setDelimiterCsv($delimiterCsv)
    {
        $this->delimiterCsv = $delimiterCsv;
    }

    public function setTitleDisplayed($titleDisplayed)
    {
        $this->titleDisplayed = $titleDisplayed;
    }

    public function setMatchField($matchField)
    {
        $this->matchField = $matchField;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log)
    {
        $this->log = $log;
    }

}
