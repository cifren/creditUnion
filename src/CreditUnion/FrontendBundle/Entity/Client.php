<?php

namespace CreditUnion\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CreditUnion\FrontendBundle\Entity\Client
 * 
 * @ORM\Entity
 * @ORM\Table(name="client")
 */
class Client {

  static protected $importColumnNames = array(
      'name' => array('display' => 'name', 'enabled' => false),
      'accountNumber' => array('display' => 'account number', 'enabled' => false),
      'panNumber' => array('display' => 'pan number', 'enabled' => false),
      'address' => array('display' => 'address', 'enabled' => false),
      'city' => array('display' => 'city', 'enabled' => false),
      'province' => array('display' => 'province', 'enabled' => false),
      'postal' => array('display' => 'postal', 'enabled' => false),
      'birthDate' => array('display' => 'birth', 'enabled' => false),
      'branch' => array('display' => 'branch', 'enabled' => false)
  );

  /**
   * @ORM\Id
   * @ORM\Column(type="bigint")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="string", length=255)
   */
  protected $name;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  protected $accountNumber;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  protected $panNumber;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected $address;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  protected $city;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  protected $province;

  /**
   * @ORM\Column(type="string", length=20, nullable=true)
   */
  protected $postal;

  /**
   * @ORM\Column(type="date", nullable=true)
   */
  protected $birthDate;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  protected $branch;

  /**
   * @ORM\ManyToOne(targetEntity="Fininstitut", inversedBy="clients")
   * @ORM\JoinColumn(name="fininstitut_id", referencedColumnName="id", nullable=false)
   */
  protected $fininstitut;

  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getAccountNumber()
  {
    return $this->accountNumber;
  }

  public function getPanNumber()
  {
    return $this->panNumber;
  }

  public function getAddress()
  {
    return $this->address;
  }

  public function getCity()
  {
    return $this->city;
  }

  public function getProvince()
  {
    return $this->province;
  }

  public function getPostal()
  {
    return $this->postal;
  }

  public function getBirthDate()
  {
    return $this->birthDate;
  }

  public function getFininstitut()
  {
    return $this->fininstitut;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function setAccountNumber($accountNumber)
  {
    $this->accountNumber = $accountNumber;
  }

  public function setPanNumber($panNumber)
  {
    $this->panNumber = $panNumber;
  }

  public function setAddress($address)
  {
    $this->address = $address;
  }

  public function setCity($city)
  {
    $this->city = $city;
  }

  public function setProvince($province)
  {
    $this->province = $province;
  }

  public function setPostal($postal)
  {
    $this->postal = $postal;
  }

  public function setBirthDate($birthDate)
  {
    $this->birthDate = $birthDate;
  }

  public function setFininstitut($fininstitut)
  {
    $this->fininstitut = $fininstitut;
  }

  static public function getImportColumnNames()
  {
    return self::$importColumnNames;
  }

  public function set($name, $value)
  {
    $method = 'set' . ucfirst($name);
    if (method_exists($this, $method)) {
      return $this->$method($value);
    }
  }

  public function getBranch()
  {
    return $this->branch;
  }

  public function setBranch($branch)
  {
    $this->branch = $branch;
    return $this;
  }

}
