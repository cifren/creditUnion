<?php

namespace CreditUnion\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\FrontendBundle\Entity\branch
 * 
 * @ORM\Entity(repositoryClass="CreditUnion\FrontendBundle\Repository\BranchRepository")
 * @ORM\Table(name="branch")
 */
class Branch
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * 
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Client", mappedBy="branch", cascade={"all"})
     */
    protected $clients;

    /**
     * @ORM\OneToOne(targetEntity="CreditUnion\BackendBundle\Entity\ImportFormat", mappedBy="branch", cascade={"all"})
     */
    protected $importFormat;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setClients($clients)
    {
        $this->clients = $clients;
    }

    public function getImportFormat()
    {
        return $this->importFormat;
    }

    public function setImportFormat($importFormat)
    {
        $this->importFormat = $importFormat;
    }

}
