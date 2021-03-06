<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\Province
*
* @ORM\Table(name="Provincias")
* @ORM\Entity(repositoryClass="EC\PrincipalBundle\Entity\ProvinceRepository")
*/
class Province
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
* @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Country", inversedBy="provinces")
* @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
*/
    protected $country;

/**
* @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\City", mappedBy="province")
*/
    protected $cities;

/**
* Constructor
*/
    public function __construct()
    {
        $this->cities = new \Doctrine\Common\Collections\ArrayCollection();
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
* @return Province
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
* Set country
*
* @param \EC\PrincipalBundle\Entity\Country $country
* @return Province
*/
    public function setCountry(\EC\PrincipalBundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

/**
* Get country
*
* @return \EC\PrincipalBundle\Entity\Country
*/
    public function getCountry()
    {
        return $this->country;
    }

/**
* Add cities
*
* @param \EC\PrincipalBundle\Entity\City $cities
* @return Province
*/
    public function addCity(\EC\PrincipalBundle\Entity\City $cities)
    {
        $this->cities[] = $cities;

        return $this;
    }

/**
* Remove cities
*
* @param \EC\PrincipalBundle\Entity\City $cities
*/
    public function removeCity(\EC\PrincipalBundle\Entity\City $cities)
    {
        $this->cities->removeElement($cities);
    }

    public function __toString()
    {
        return $this->name;
    }

/**
* Get cities
*
* @return \Doctrine\Common\Collections\Collection
*/
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * Add cities
     *
     * @param \EC\PrincipalBundle\Entity\City $cities
     * @return Province
     */
    public function addCitie(\EC\PrincipalBundle\Entity\City $cities)
    {
        $this->cities[] = $cities;
    
        return $this;
    }

    /**
     * Remove cities
     *
     * @param \EC\PrincipalBundle\Entity\City $cities
     */
    public function removeCitie(\EC\PrincipalBundle\Entity\City $cities)
    {
        $this->cities->removeElement($cities);
    }
}