<?php

namespace EC\ComunidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="EC\ComunidadBundle\Entity\ComunidadRepository")
 * @ORM\Table(name="Comunidades")
 */
class Comunidad
{	
	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Id
     * @ORM\Column(name="cif",type="string",unique=true,length=9)
     */
    protected $cif;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="direccion",type="string", length=150)
     */
    protected $direccion;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="provincia",type="string", length=100)
     */
    protected $provincia;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="localidad",type="string", length=100)
     */
    protected $localidad;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="n_plazas_garaje",type="integer")
     */
    protected $n_plazas_garaje;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="n_locales_comerciales",type="integer")
     */
    protected $n_locales_comerciales;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="n_piscinas",type="integer")
     */
    protected $n_piscinas;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="n_pistas",type="integer")
     */
    protected $n_pistas;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="gimnasio",type="boolean")
     */
    protected $gimnasio;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="ascensor",type="boolean")
     */
    protected $ascensor;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="conserjeria",type="boolean")
     */
    protected $conserjeria;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\AdminFincasBundle\Entity\AdminFincas", inversedBy="comunidades")
     * @ORM\JoinColumn(name="n_colegiado_admin", referencedColumnName="n_colegiado_admin")
     */
    protected $administrador;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\VecinoBundle\Entity\Vecino", mappedBy="comunidad")
     */
    protected $vecinos;
 
    public function __construct()
    {
        $this->vecinos = new ArrayCollection();
    }

    /**
     * Set cif
     *
     * @param string $cif
     * @return Comunidad
     */
    public function setCif($cif)
    {
        $this->cif = $cif;
    
        return $this;
    }

    /**
     * Get cif
     *
     * @return string 
     */
    public function getCif()
    {
        return $this->cif;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Comunidad
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     * @return Comunidad
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return string 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return Comunidad
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set n_plazas_garaje
     *
     * @param integer $nPlazasGaraje
     * @return Comunidad
     */
    public function setNPlazasGaraje($nPlazasGaraje)
    {
        $this->n_plazas_garaje = $nPlazasGaraje;
    
        return $this;
    }

    /**
     * Get n_plazas_garaje
     *
     * @return integer 
     */
    public function getNPlazasGaraje()
    {
        return $this->n_plazas_garaje;
    }

    /**
     * Set n_locales_comerciales
     *
     * @param integer $nLocalesComerciales
     * @return Comunidad
     */
    public function setNLocalesComerciales($nLocalesComerciales)
    {
        $this->n_locales_comerciales = $nLocalesComerciales;
    
        return $this;
    }

    /**
     * Get n_locales_comerciales
     *
     * @return integer 
     */
    public function getNLocalesComerciales()
    {
        return $this->n_locales_comerciales;
    }

    /**
     * Set n_piscinas
     *
     * @param integer $nPiscinas
     * @return Comunidad
     */
    public function setNPiscinas($nPiscinas)
    {
        $this->n_piscinas = $nPiscinas;
    
        return $this;
    }

    /**
     * Get n_piscinas
     *
     * @return integer 
     */
    public function getNPiscinas()
    {
        return $this->n_piscinas;
    }

    /**
     * Set gimnasio
     *
     * @param integer $gimnasio
     * @return Comunidad
     */
    public function setGimnasio($gimnasio)
    {
        $this->gimnasio = $gimnasio;
    
        return $this;
    }

    /**
     * Get gimnasio
     *
     * @return integer 
     */
    public function getGimnasio()
    {
        return $this->gimnasio;
    }

    /**
     * Set ascensor
     *
     * @param boolean $ascensor
     * @return Comunidad
     */
    public function setAscensor($ascensor)
    {
        $this->ascensor = $ascensor;
    
        return $this;
    }

    /**
     * Get ascensor
     *
     * @return boolean 
     */
    public function getAscensor()
    {
        return $this->ascensor;
    }

    /**
     * Set conserjeria
     *
     * @param boolean $conserjeria
     * @return Comunidad
     */
    public function setConserjeria($conserjeria)
    {
        $this->conserjeria = $conserjeria;
    
        return $this;
    }

    /**
     * Get conserjeria
     *
     * @return boolean 
     */
    public function getConserjeria()
    {
        return $this->conserjeria;
    }

    /**
     * Set administrador
     *
     * @param \EC\AdminFincasBundle\Entity\AdminFincas $administrador
     * @return Comunidad
     */
    public function setAdministrador(\EC\AdminFincasBundle\Entity\AdminFincas $administrador = null)
    {
        $this->administrador = $administrador;
    
        return $this;
    }

    /**
     * Get administrador
     *
     * @return \EC\AdminFincasBundle\Entity\AdminFincas 
     */
    public function getAdministrador()
    {
        return $this->administrador;
    }

    /**
     * Set n_pistas
     *
     * @param integer $nPistas
     * @return Comunidad
     */
    public function setNPistas($nPistas)
    {
        $this->n_pistas = $nPistas;
    
        return $this;
    }

    /**
     * Get n_pistas
     *
     * @return integer 
     */
    public function getNPistas()
    {
        return $this->n_pistas;
    }

    /**
     * Add vecinos
     *
     * @param \EC\VecinoBundle\Entity\Vecino $vecinos
     * @return Comunidad
     */
    public function addVecino(\EC\VecinoBundle\Entity\Vecino $vecinos)
    {
        $this->vecinos[] = $vecinos;
    
        return $this;
    }

    /**
     * Remove vecinos
     *
     * @param \EC\VecinoBundle\Entity\Vecino $vecinos
     */
    public function removeVecino(\EC\VecinoBundle\Entity\Vecino $vecinos)
    {
        $this->vecinos->removeElement($vecinos);
    }

    /**
     * Get vecinos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVecinos()
    {
        return $this->vecinos;
    }
}