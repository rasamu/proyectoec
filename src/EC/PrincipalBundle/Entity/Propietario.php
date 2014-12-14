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
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Incidencia", mappedBy="propietario")
     */
    protected $incidencias;
    
    /**
     * @ORM\OneToOne(targetEntity="EC\PrincipalBundle\Entity\Propiedad", inversedBy="propietario")
     * @ORM\JoinColumn(name="id_propiedad", referencedColumnName="id")
     **/
    private $propiedad;
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->incidencias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actuaciones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
}