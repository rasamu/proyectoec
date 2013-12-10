<?php

namespace EC\VecinoBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Vecinos")
 */
class Vecino implements UserInterface
{	
	function equals(UserInterface $vecino)
	{
		return $this->getDni() == $vecino->getDni();	
	}
	
	function eraseCredentials()
	{
	}
	
	function getRoles()
	{
		return array('ROLE_VECINO');	
	}
	
	function getUsername()
	{
		return $this->getDni();
	}
	
	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Id
     * @ORM\Column(name="dni_vecino",type="string",unique=true)
     */
    protected $dni;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="tipo_vecino",type="string", length=10)
     */
    protected $tipo="vecino";

    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre_vecino",type="string", length=100)
     */
    protected $nombre;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="apellidos_vecino",type="string", length=100)
     */
    protected $apellidos;

	/**
	  * @Assert\NotNull()
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="integer")
     * @ORM\Column(name="telefono_vecino",type="string",length=9)
     */
    protected $telefono;
    
    /**
	  * @Assert\NotNull()
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es un email válido.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email_vecino",type="string", length=100)
     */
    protected $email;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="portal_vecino",type="string", length=10)
     */
    protected $portal;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="piso_vecino",type="string", length=10)
     */
    protected $piso;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="provincia_vecino",type="string", length=100)
     */
    protected $provincia;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="localidad_vecino",type="string", length=100)
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
     * @ORM\ManyToOne(targetEntity="EC\ComunidadBundle\Entity\Comunidad", inversedBy="vecinos")
     * @ORM\JoinColumn(name="cif", referencedColumnName="cif")
     */
    protected $comunidad;

    /**
     * Set dni
     *
     * @param string $dni
     * @return Vecino
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
     * Set tipo
     *
     * @param string $tipo
     * @return Vecino
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Vecino
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
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
     * @return Vecino
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
     * @return Vecino
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
     * @return Vecino
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
     * Set portal
     *
     * @param string $portal
     * @return Vecino
     */
    public function setPortal($portal)
    {
        $this->portal = $portal;
    
        return $this;
    }

    /**
     * Get portal
     *
     * @return string 
     */
    public function getPortal()
    {
        return $this->portal;
    }

	/**
     * Set piso
     *
     * @param string $piso
     * @return Vecino
     */
    public function setPiso($piso)
    {
        $this->piso = $piso;
    
        return $this;
    }

    /**
     * Get piso
     *
     * @return string 
     */
    public function getPiso()
    {
        return $this->piso;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     * @return Vecino
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
     * @return Vecino
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
     * Set password
     *
     * @param string $password
     * @return Vecino
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
     * @return Vecino
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
     * Set comunidad
     *
     * @param \EC\ComunidadBundle\Entity\Comunidad $comunidad
     * @return Vecino
     */
    public function setComunidad(\EC\ComunidadBundle\Entity\Comunidad $comunidad = null)
    {
        $this->comunidad = $comunidad;
    
        return $this;
    }

    /**
     * Get comunidad
     *
     * @return \EC\ComunidadBundle\Entity\Comunidad 
     */
    public function getComunidad()
    {
        return $this->comunidad;
    }
}