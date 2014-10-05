<?php

namespace EC\PrincipalBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity
 * @ORM\Table(name="Usuarios")
 * @ORM\HasLifecycleCallbacks
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"propietario"="Usuario","adminfincas" = "EC\PrincipalBundle\Entity\AdminFincas"})
 */
class Usuario implements UserInterface, \Serializable
{
	function equals(UserInterface $usuario)
	{
		return $this->getId() == $usuario->getId();	
	}
	
	function eraseCredentials()
	{
	}
	
	function getRoles()
	{
		$role=$this->getRole();
		return array($role->getNombre());
	}
	
	function getUsername()
	{
		return $this->getUser();
	}
	
	/**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->user,
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
            $this->user,
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
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre",type="string", length=100)
     */
    protected $nombre;

	/**
     * @ORM\Column(name="apellidos",type="string", length=100,nullable=true)
     */
    protected $apellidos;

	/**
     * @ORM\Column(name="telefono",type="string",length=9,nullable=true)
     */
    protected $telefono;
    
    /**
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es un email válido.",
     *     checkMX = true
     * )
     * @ORM\Column(name="email",type="string", length=100,nullable=true)
     */
    protected $email;
    
    /**
     * @ORM\Column(name="fecha_alta",type="datetime")
     */
    protected $fecha_alta;
    
    /**
     * @Assert\Type(type="string")
     * @ORM\Column(name="user",type="string",unique=true)
     */
    protected $user;
    
    /**
     * @Assert\Length(
     *      min=6
     * )
     * @Assert\Type(type="string")
     * @ORM\Column(name="password",type="string", length=255)
     */
    protected $password;

	/**
     * @ORM\Column(name="salt",type="string", length=255)
     */
    protected $salt;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Role", inversedBy="usuarios")
     * @ORM\JoinColumn(name="role", referencedColumnName="id")
     */
    protected $role;
    
    /**
     * @ORM\OneToOne(targetEntity="EC\PrincipalBundle\Entity\Propiedad", inversedBy="propietario")
     * @ORM\JoinColumn(name="id_propiedad", referencedColumnName="id")
     **/
    private $propiedad;

	 /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Incidencia", mappedBy="usuario")
     */
    protected $incidencias;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Actuacion", mappedBy="usuario")
     */
    protected $actuaciones;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Log", mappedBy="usuario")
     */
    protected $logs;
    
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
     * @return Usuario
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
     * Set email
     *
     * @param string $email
     * @return Usuario
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
     * @return Usuario
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
     * @return Usuario
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
     * Set user
     *
     * @param string $user
     * @return Usuario
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Set role
     *
     * @param \EC\PrincipalBundle\Entity\Role $role
     * @return Usuario
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
     * Set propiedad
     *
     * @param \EC\PrincipalBundle\Entity\Propiedad $propiedad
     * @return Usuario
     */
    public function setPropiedad(\EC\PrincipalBundle\Entity\Propiedad $propiedad = null)
    {
        $this->propiedad = $propiedad;
    
        return $this;
    }

    /**
     * Get propiedad
     *
     * @return \EC\PrincipalBundle\Entity\Propiedad 
     */
    public function getPropiedad()
    {
        return $this->propiedad;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->incidencias = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add incidencias
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencias
     * @return Usuario
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
     * Add actuaciones
     *
     * @param \EC\PrincipalBundle\Entity\Actuacion $actuaciones
     * @return Usuario
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
     * @return Usuario
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
     * Get log
     *
     * @return \EC\PrincipalBundle\Entity\Log 
     */
    public function getUltimoLog()
    {
        $logs=$this->logs;
        return $logs->Last();
    }
}