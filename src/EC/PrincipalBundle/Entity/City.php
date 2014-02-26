<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\City
*
* @ORM\Table(name="main_city")
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
* @ORM\Column(name="name", type="string", length=255)
*/
    protected $name;
    
    /**
* @var string $slug
*
* @ORM\Column(name="slug", type="string", length=255, unique=true)
*/
    protected $slug;

/**
* @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Province", inversedBy="cities")
* @ORM\JoinColumn(name="province_id", referencedColumnName="id")
*/
    protected $province;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\ComunidadBundle\Entity\Comunidad", mappedBy="city")
     */
    protected $comunidades;

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
* Set slug
*
* @param string $slug
* @return City
*/
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
* Get slug
*
* @return string
*/
    public function getSlug()
    {
        return $this->slug;
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
     * @param \EC\ComunidadBundle\Entity\Comunidad $comunidades
     * @return City
     */
    public function addComunidade(\EC\ComunidadBundle\Entity\Comunidad $comunidades)
    {
        $this->comunidades[] = $comunidades;
    
        return $this;
    }

    /**
     * Remove comunidades
     *
     * @param \EC\ComunidadBundle\Entity\Comunidad $comunidades
     */
    public function removeComunidade(\EC\ComunidadBundle\Entity\Comunidad $comunidades)
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
}