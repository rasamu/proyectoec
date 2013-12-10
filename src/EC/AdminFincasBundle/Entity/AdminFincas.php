<?php

namespace EC\AdminFincasBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="AdminFincas")
 */
class AdminFincas implements UserInterface
{
	function equals(UserInterface $adminfincas)
	{
		return $this->getNColegiado() == $adminfincas->getNColegiado();	
	}
	
	function eraseCredentials()
	{
	}
	
	function getRoles()
	{
		return array('ROLE_ADMINFINCAS');	
	}
	
	function getUsername()
	{
		return $this->getNColegiado();
	}
	
	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Id
     * @ORM\Column(name="n_colegiado_admin",type="string",unique=true,length=9)
     */
    protected $n_colegiado;

    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre_admin",type="string", length=100)
     */
    protected $nombre;

	/**
	  * @Assert\NotNull()
     * @ORM\Column(name="apellidos_admin",type="string", length=100)
     */
    protected $apellidos;

	/**
	  * @Assert\NotNull()
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="integer")
     * @ORM\Column(name="telefono_admin",type="string",length=9)
     */
    protected $telefono;
    
    /**
	  * @Assert\NotNull()
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="integer")
     * @ORM\Column(name="fax_admin",type="string",length=9)
     */
    protected $fax; 
    
    /**
	  * @Assert\NotNull()
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es un email válido.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email_admin",type="string", length=100)
     */
    protected $email;
    
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
	  * @Assert\NotNull()
     * @ORM\Column(name="password",type="string", length=255)
     */
    protected $password;

	/**
     * @ORM\Column(name="salt",type="string", length=255)
     */
    protected $salt;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\ComunidadBundle\Entity\Comunidad", mappedBy="administrador")
     */
    protected $comunidades;
 
    public function __construct()
    {
        $this->comunidades = new ArrayCollection();
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
     * @param integer $telefono
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
     * @return integer 
     */
    public function getTelefono()
    {
        return $this->telefono;
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
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
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
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
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


}