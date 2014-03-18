<?php

namespace EC\ComunidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="EC\ComunidadBundle\Entity\ComunidadRepository")
 * @ORM\Table(name="Comunidades")
 * @ORM\HasLifecycleCallbacks
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
     * @Assert\Type(type="integer")
     * @ORM\Column(name="codigo_despacho",type="integer",length=9,nullable=true)
     */
    protected $codigo;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="direccion",type="string", length=150)
     */
    protected $direccion;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="n_plazas_garaje",type="integer")
     */
    protected $n_plazas_garaje;
    
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
     * @ORM\Column(name="fecha_alta",type="datetime")
     */
    protected $fecha_alta;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\AdminFincasBundle\Entity\AdminFincas", inversedBy="comunidades")
     * @ORM\JoinColumn(name="dni_administrador", referencedColumnName="dni_admin")
     */
    protected $administrador;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PropietarioBundle\Entity\Propietario", mappedBy="comunidad")
     */
    protected $propietarios;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\City", inversedBy="comunidades")
     * @ORM\JoinColumn(name="city", referencedColumnName="id")
     */
    protected $city;
 
    public function __construct()
    {
        $this->propietarios = new ArrayCollection();
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
     * Set fecha_alta
	  * @ORM\PrePersist()
     */
    public function setFechaAlta($fechaAlta = null)
    {
        $this->fecha_alta = null === $fechaAlta ? new \DateTime() : $fechaAlta;
    
        return $this;
    }

    /**
     * Get fecha_alta
     *
     * @return \DateTime 
     */
    public function getFechaAlta()
    {
        return $this->fecha_alta;
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
     * Add propietarios
     *
     * @param \EC\PropietarioBundle\Entity\Propietario $propietarios
     * @return Comunidad
     */
    public function addPropietario(\EC\PropietarioBundle\Entity\Propietario $propietarios)
    {
        $this->propietarios[] = $propietarios;
    
        return $this;
    }

    /**
     * Remove propietarios
     *
     * @param \EC\PropietarioBundle\Entity\Propietario $propietarios
     */
    public function removePropietario(\EC\PropietarioBundle\Entity\Propietario $propietarios)
    {
        $this->propietarios->removeElement($propietarios);
    }

    /**
     * Get propietarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropietarios()
    {
        return $this->propietarios;
    }


    /**
     * Set city
     *
     * @param \EC\PrincipalBundle\Entity\City $city
     * @return Comunidad
     */
    public function setCity(\EC\PrincipalBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return \EC\PrincipalBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set codigo
     *
     * @param integer $codigo
     * @return Comunidad
     */
    public function setCodigo($codigo=null)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }
}