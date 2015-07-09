<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\Country
*
* @ORM\Table(name="Paises")
* @ORM\Entity()
*/
class Country
{
/**
* @var integer $id
*
* @ORM\Column(name="id", type="integer")
* @ORM\Id
* @ORM\GeneratedValue(strategy="AUTO")
*/
    protected $id;

/**
* @var string $name
*
* @ORM\Column(name="name", type="string", length=100)
*/
    protected $name;

/**
* @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Province", mappedBy="country")
*/
    protected $provinces;

/**
* Constructor
*/
    public function __construct()
    {
        $this->provinces = new \Doctrine\Common\Collections\ArrayCollection();
    }

/**
* Get id
*
* @return integer
*/
    public function getId()
    {
        return $this->id;
    }

/**
* Set name
*
* @param string $name
* @return Country
*/
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

/**
* Get name
*
* @return string
*/
    public function getName()
    {
        return $this->name;
    }

/**
* Add provinces
*
* @param \EC\PrincipalBundle\Entity\Province $provinces
* @return Country
*/
    public function addProvince(\EC\PrincipalBundle\Entity\Province $provinces)
    {
        $this->provinces[] = $provinces;

        return $this;
    }

/**
* Remove provinces
*
* @param \EC\PrincipalBundle\Entity\Province $provinces
*/
    public function removeProvince(\EC\PrincipalBundle\Entity\Province $provinces)
    {
        $this->provinces->removeElement($provinces);
    }

/**
* Get provinces
*
* @return \Doctrine\Common\Collections\Collection
*/
    public function getProvinces()
    {
        return $this->provinces;
    }

    public function __toString()
    {
        return $this->name;
    }
}