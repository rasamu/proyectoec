<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Role;

/**
 * @ORM\Entity
 * @ORM\Table(name="AdminFincas")
 * @ORM\HasLifecycleCallbacks
 */
class AdminFincas extends Usuario
{     
    /**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     * @ORM\Column(name="dni_admin",type="string", length=9,unique=true)
     */
    protected $dni;
    
	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Column(name="n_colegiado_admin",type="string",unique=true,length=9,nullable=true)
     */
    protected $n_colegiado=NULL;
      
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre",type="string", length=100)
     */
    protected $nombre;

	/**
     * @ORM\Column(name="apellidos",type="string", length=100,nullable=true)
     */
    protected $apellidos;
    
    /**
     * @ORM\Column(name="fax_admin",type="string",length=9,nullable=true)
     */
    protected $fax=NULL; 
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="direccion_admin",type="string", length=150)
     */
    protected $direccion;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="provincia_admin",type="string", length=100)
     */
    protected $provincia;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="localidad_admin",type="string", length=100)
     */
    protected $localidad;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Comunidad", mappedBy="administrador", cascade={"remove"})
     */
    protected $comunidades;
 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comunidades = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set n_colegiado
     *
     * @param integer $nColegiado
     * @return AdminFincas
     */
    public function setNColegiado($nColegiado)
    {
        $this->n_colegiado = $nColegiado;
    
        return $this;
    }

    /**
     * Get n_colegiado
     *
     * @return integer 
     */
    public function getNColegiado()
    {
        return $this->n_colegiado;
    }

    /**
     * Set fax
     *
     * @param integer $fax
     * @return AdminFincas
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    
        return $this;
    }

    /**
     * Get fax
     *
     * @return integer 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return AdminFincas
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
     * @return AdminFincas
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
     * @return AdminFincas
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
     * Set dni
     *
     * @param string $dni
     * @return AdminFincas
     */
    public function setDni($dni)
    {
        $this->dni = $dni;
    
        return $this;
    }

    /**
     * Get dni
     *
     * @return string 
     */
    public function getDni()
    {
        return $this->dni;
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
     * Add comunidades
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidades
     * @return AdminFincas
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
     * Set nombre
     *
     * @param string $nombre
     * @return AdminFincas
     */
    public function setNombre($name)
    {
        $this->nombre = $name;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }
    
    /**
     * Set apellidos
     *
     * @param string $apellidos
     * @return AdminFincas
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;
    
        return $this;
    }

    /**
     * Get apellidos
     *
     * @return string 
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return AdminFincas
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
     * @return AdminFincas
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
     * @return AdminFincas
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
     * @return AdminFincas
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
     * @return AdminFincas
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
     * @return AdminFincas
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $actuaciones;


    /**
     * Add actuaciones
     *
     * @param \EC\PrincipalBundle\Entity\Actuacion $actuaciones
     * @return AdminFincas
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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $logs;


    /**
     * Add logs
     *
     * @param \EC\PrincipalBundle\Entity\Log $logs
     * @return AdminFincas
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
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $anuncios;

    /**
     * Add anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     * @return AdminFincas
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
}