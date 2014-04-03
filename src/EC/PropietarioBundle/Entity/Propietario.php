<?php

namespace EC\PropietarioBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Propietarios")
 * @ORM\HasLifecycleCallbacks
 */
class Propietario implements UserInterface, \Serializable
{	
	function equals(UserInterface $propietario)
	{
		return $this->getId() == $propietario->getId();	
	}
	
	function eraseCredentials()
	{
	}
	
	function getRoles()
	{
		if($this->getTipo()=='Vecino'){
			return array('ROLE_VECINO');	
		}else{
			if($this->getTipo()=='Presidente'){
				return array('ROLE_PRESIDENTE');	
			}else{
				return array('ROLE_VICEPRESIDENTE');	
			}
		}
	}
	
	function getUsername()
	{
		return $this->getUsuario();
	}
	
	/**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->usuario,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->usuario,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
	
	/**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer",unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @Assert\Type(type="string")
     * @ORM\Column(name="usuario",type="string",unique=true)
     */
    protected $usuario;
	
	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Column(name="dni_propietario",type="string",nullable=true)
     */
    protected $dni;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="tipo_propietario",type="string", length=14)
     */
    protected $tipo="Vecino";

    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre_propietario",type="string", length=100)
     */
    protected $nombre;

	/**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="integer")
     * @ORM\Column(name="telefono_propietario",type="string",length=9,nullable=true)
     */
    protected $telefono;
    
    /**
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es un email válido.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email_propietario",type="string", length=100,nullable=true)
     */
    protected $email;
    
    /**
     * @ORM\Column(name="fecha_alta_propietario",type="datetime")
     */
    protected $fecha_alta;
    
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
     * @ORM\OneToOne(targetEntity="EC\PropietarioBundle\Entity\Propiedad", inversedBy="propietario")
     * @ORM\JoinColumn(name="id_propiedad", referencedColumnName="id")
     **/
    private $propiedad;

    /**
     * Set dni
     *
     * @param string $dni
     * @return Propietario
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
     * @return Propietario
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
     * @return Propietario
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
     * Set propiedad
     *
     * @param \EC\PropietarioBundle\Entity\Propiedad $propiedad
     * @return Propietario
     */
    public function setPropiedad(\EC\PropietarioBundle\Entity\Propiedad $propiedad = null)
    {
        $this->propiedad = $propiedad;
    
        return $this;
    }

    /**
     * Get propiedad
     *
     * @return \EC\PropietarioBundle\Entity\Propiedad 
     */
    public function getPropiedad()
    {
        return $this->propiedad;
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
     * Set usuario
     *
     * @param string $usuario
     * @return Propietario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return string 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}