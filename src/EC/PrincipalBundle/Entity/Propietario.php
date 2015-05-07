<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Role;

/**
 * @ORM\Entity
 * @ORM\Table(name="Propietario")
 * @ORM\HasLifecycleCallbacks
 */
class Propietario extends Usuario
{     
	 /**
	  * @Assert\NotNull()
     * @ORM\Column(name="razon",type="string", length=100)
     */ 
    protected $razon;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Incidencia", mappedBy="propietario", cascade={"remove"})
     */
    protected $incidencias;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="propiedad",type="string", length=155)
     */
    protected $propiedad;

	/**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Bloque", inversedBy="propietarios")
     * @ORM\JoinColumn(name="id_bloque", referencedColumnName="id")
     */
    protected $bloque;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\ConsultaDocumento", mappedBy="propietario", cascade={"remove"})
     */
    private $consultas_documentos;	
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->incidencias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->consultas_documentos = new \Doctrine\Common\Collections\ArrayCollection();
    } 
    
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $telefono;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var \DateTime
     */
    protected $fecha_alta;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var \EC\PrincipalBundle\Entity\Role
     */
    protected $role;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $actuaciones;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $logs;
    
    /**
     * Set razon
     *
     * @param string $razon
     * @return Propietario
     */
    public function setRazon($razon)
    {
        $this->razon = $razon;
    
        return $this;
    }

    /**
     * Get razon
     *
     * @return string 
     */
    public function getRazon()
    {
        return $this->razon;
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
     * Set telefono
     *
     * @param string $telefono
     * @return Propietario
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Propietario
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
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
     * Set user
     *
     * @param string $user
     * @return Propietario
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Propietario
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Propietario
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set role
     *
     * @param \EC\PrincipalBundle\Entity\Role $role
     * @return Propietario
     */
    public function setRole(\EC\PrincipalBundle\Entity\Role $role = null)
    {
        $this->role = $role;
    
        return $this;
    }

    /**
     * Get role
     *
     * @return \EC\PrincipalBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add actuaciones
     *
     * @param \EC\PrincipalBundle\Entity\Actuacion $actuaciones
     * @return Propietario
     */
    public function addActuacione(\EC\PrincipalBundle\Entity\Actuacion $actuaciones)
    {
        $this->actuaciones[] = $actuaciones;
    
        return $this;
    }

    /**
     * Remove actuaciones
     *
     * @param \EC\PrincipalBundle\Entity\Actuacion $actuaciones
     */
    public function removeActuacione(\EC\PrincipalBundle\Entity\Actuacion $actuaciones)
    {
        $this->actuaciones->removeElement($actuaciones);
    }

    /**
     * Get actuaciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActuaciones()
    {
        return $this->actuaciones;
    }

    /**
     * Add logs
     *
     * @param \EC\PrincipalBundle\Entity\Log $logs
     * @return Propietario
     */
    public function addLog(\EC\PrincipalBundle\Entity\Log $logs)
    {
        $this->logs[] = $logs;
    
        return $this;
    }

    /**
     * Remove logs
     *
     * @param \EC\PrincipalBundle\Entity\Log $logs
     */
    public function removeLog(\EC\PrincipalBundle\Entity\Log $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add incidencias
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencias
     * @return Propietario
     */
    public function addIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencias)
    {
        $this->incidencias[] = $incidencias;
    
        return $this;
    }

    /**
     * Remove incidencias
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencias
     */
    public function removeIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencias)
    {
        $this->incidencias->removeElement($incidencias);
    }

    /**
     * Get incidencias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncidencias()
    {
        return $this->incidencias;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $anuncios;


    /**
     * Add anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     * @return Propietario
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

    /**
     * Set propiedad
     *
     * @param string $propiedad
     * @return Propietario
     */
    public function setPropiedad($propiedad)
    {
        $this->propiedad = $propiedad;
    
        return $this;
    }

    /**
     * Get propiedad
     *
     * @return string 
     */
    public function getPropiedad()
    {
        return $this->propiedad;
    }

    /**
     * Set bloque
     *
     * @param \EC\PrincipalBundle\Entity\Bloque $bloque
     * @return Propietario
     */
    public function setBloque(\EC\PrincipalBundle\Entity\Bloque $bloque = null)
    {
        $this->bloque = $bloque;
    
        return $this;
    }

    /**
     * Get bloque
     *
     * @return \EC\PrincipalBundle\Entity\Bloque 
     */
    public function getBloque()
    {
        return $this->bloque;
    }  

    /**
     * Add consultas_documentos
     *
     * @param \EC\PrincipalBundle\Entity\ConsultaDocumento $consultasDocumentos
     * @return Propietario
     */
    public function addConsultasDocumento(\EC\PrincipalBundle\Entity\ConsultaDocumento $consultasDocumentos)
    {
        $this->consultas_documentos[] = $consultasDocumentos;
    
        return $this;
    }

    /**
     * Remove consultas_documentos
     *
     * @param \EC\PrincipalBundle\Entity\ConsultaDocumento $consultasDocumentos
     */
    public function removeConsultasDocumento(\EC\PrincipalBundle\Entity\ConsultaDocumento $consultasDocumentos)
    {
        $this->consultas_documentos->removeElement($consultasDocumentos);
    }

    /**
     * Get consultas_documentos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConsultasDocumentos()
    {
        return $this->consultas_documentos;
    }
}