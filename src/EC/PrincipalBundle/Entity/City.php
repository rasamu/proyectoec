<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\City
*
* @ORM\Table(name="Ciudades")
* @ORM\Entity(repositoryClass="EC\PrincipalBundle\Entity\CityRepository")
*/
class City
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
* @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Province", inversedBy="cities")
* @ORM\JoinColumn(name="province_id", referencedColumnName="id", nullable=false)
*/
    protected $province;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Comunidad", mappedBy="city")
     */
    protected $comunidades;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Servicio", mappedBy="city")
     */
    protected $servicios;

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
* @return City
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
* Set province
*
* @param \EC\PrincipalBundle\Entity\Province $province
* @return City
*/
    public function setProvince(\EC\PrincipalBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

/**
* Get province
*
* @return \EC\PrincipalBundle\Entity\Province
*/
    public function getProvince()
    {
        return $this->province;
    }

    public function __toString()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comunidades = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add comunidades
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidades
     * @return City
     */
    public function addComunidade(\EC\PrincipalBundle\Entity\Comunidad $comunidades)
    {
        $this->comunidades[] = $comunidades;
    
        return $this;
    }

    /**
     * Remove comunidades
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidades
     */
    public function removeComunidade(\EC\PrincipalBundle\Entity\Comunidad $comunidades)
    {
        $this->comunidades->removeElement($comunidades);
    }

    /**
     * Get comunidades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComunidades()
    {
        return $this->comunidades;
    }

    /**
     * Add servicios
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicios
     * @return City
     */
    public function addServicio(\EC\PrincipalBundle\Entity\Servicio $servicios)
    {
        $this->servicios[] = $servicios;
    
        return $this;
    }

    /**
     * Remove servicios
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicios
     */
    public function removeServicio(\EC\PrincipalBundle\Entity\Servicio $servicios)
    {
        $this->servicios->removeElement($servicios);
    }

    /**
     * Get servicios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServicios()
    {
        return $this->servicios;
    }
}