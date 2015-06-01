<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="EC\PrincipalBundle\Entity\ComunidadRepository")
 * @ORM\Table(name="Comunidades")
 * @ORM\HasLifecycleCallbacks
 */
class Comunidad
{	
	/**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer",unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Column(name="cif",type="string",unique=true,length=9)
     */
    protected $cif;
    
    /**
	  * @Assert\Type(type="string")
     * @ORM\Column(name="codigo_despacho",type="string",length=9)
     */
    protected $codigo;
    
    /**
     * @ORM\Column(name="fecha_alta",type="datetime")
     */
    protected $fecha_alta;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\AdminFincas", inversedBy="comunidades")
     * @ORM\JoinColumn(name="adminfincas", referencedColumnName="id")
     */
    protected $administrador;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\City", inversedBy="comunidades")
     * @ORM\JoinColumn(name="city", referencedColumnName="id")
     */
    protected $city;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Bloque", mappedBy="comunidad", cascade={"remove"})
     */
    protected $bloques;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Documento", mappedBy="comunidad", cascade={"remove"})
     */
    protected $documentos;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Anuncio", mappedBy="comunidad", cascade={"remove"})
     */
    protected $anuncios;
    
	 /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Reunion", mappedBy="comunidad", cascade={"remove"})
     */
    protected $reuniones;
    
    /**
     * @ORM\ManyToMany(targetEntity="EC\PrincipalBundle\Entity\Servicio", inversedBy="comunidades")
     * @ORM\JoinTable(name="comunidades_servicios")
     */
    protected $servicios;
 
    public function __construct()
    {
        $this->bloques = new ArrayCollection();
        $this->servicios = new ArrayCollection();
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
     * @param \EC\PrincipalBundle\Entity\AdminFincas $administrador
     * @return Comunidad
     */
    public function setAdministrador(\EC\PrincipalBundle\Entity\AdminFincas $administrador = null)
    {
        $this->administrador = $administrador;
    
        return $this;
    }

    /**
     * Get administrador
     *
     * @return \EC\PrincipalBundle\Entity\AdminFincas 
     */
    public function getAdministrador()
    {
        return $this->administrador;
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
     * Add bloques
     *
     * @param \EC\PrincipalBundle\Entity\Bloque $bloques
     * @return Comunidad
     */
    public function addBloque(\EC\PrincipalBundle\Entity\Bloque $bloques)
    {
        $this->bloques[] = $bloques;
    
        return $this;
    }

    /**
     * Remove bloques
     *
     * @param \EC\PrincipalBundle\Entity\Bloque $bloques
     */
    public function removeBloque(\EC\PrincipalBundle\Entity\Bloque $bloques)
    {
        $this->bloques->removeElement($bloques);
    }

    /**
     * Get bloques
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBloques()
    {
        return $this->bloques;
    }

    /**
     * Set codigo
     *
     * @param integer $codigo
     * @return Comunidad
     */
    public function setCodigo($codigo)
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

    /**
     * Add documentos
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documentos
     * @return Comunidad
     */
    public function addDocumento(\EC\PrincipalBundle\Entity\Documento $documentos)
    {
        $this->documentos[] = $documentos;
    
        return $this;
    }

    /**
     * Remove documentos
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documentos
     */
    public function removeDocumento(\EC\PrincipalBundle\Entity\Documento $documentos)
    {
        $this->documentos->removeElement($documentos);
    }

    /**
     * Get documentos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocumentos()
    {
        return $this->documentos;
    }

    /**
     * Add reuniones
     *
     * @param \EC\PrincipalBundle\Entity\Reunion $reuniones
     * @return Comunidad
     */
    public function addReunione(\EC\PrincipalBundle\Entity\Reunion $reuniones)
    {
        $this->reuniones[] = $reuniones;
    
        return $this;
    }

    /**
     * Remove reuniones
     *
     * @param \EC\PrincipalBundle\Entity\Reunion $reuniones
     */
    public function removeReunione(\EC\PrincipalBundle\Entity\Reunion $reuniones)
    {
        $this->reuniones->removeElement($reuniones);
    }

    /**
     * Get reuniones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReuniones()
    {
        return $this->reuniones;
    }

    /**
     * Add anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     * @return Comunidad
     */
    public function addAnuncio(\EC\PrincipalBundle\Entity\Anuncio $anuncios)
    {
        $this->anuncios[] = $anuncios;
    
        return $this;
    }

    /**
     * Remove anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     */
    public function removeAnuncio(\EC\PrincipalBundle\Entity\Anuncio $anuncios)
    {
        $this->anuncios->removeElement($anuncios);
    }

    /**
     * Get anuncios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnuncios()
    {
        return $this->anuncios;
    }
    
    public function getNBloques()
    {
    	  $bloques=$this->getBloques();
    	  return $bloques->count();
    }
    
    public function getNPropietarios()
    {
    	  $bloques=$this->getBloques();
    	  $count=0;
    	  foreach($bloques as $bloque){
    	  		$propietarios=$bloque->getPropietarios();
    	  		$count=$count + $propietarios->count();	
    	  }
        return $count;
    }

    /**
     * Add servicios
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicios
     * @return Comunidad
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