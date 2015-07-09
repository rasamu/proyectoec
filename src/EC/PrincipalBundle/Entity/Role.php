<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\Role
*
* @ORM\Entity
* @ORM\Table(name="Roles")
*/
class Role
{
/**
*
* @ORM\Column(name="id", type="integer",unique=true)
* @ORM\Id
* @ORM\GeneratedValue(strategy="AUTO")
*/
    protected $id;

/**
* @var string $nombre
*
* @ORM\Column(name="nombre", type="string", length=100)
*/
    protected $nombre;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Usuario", mappedBy="role")
     */
    protected $usuarios;

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
* Set nombre
*
* @param string $nombre
* @return Role
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
     * Constructor
     */
    public function __construct()
    {
        $this->usuarios = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add usuarios
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $usuarios
     * @return Role
     */
    public function addUsuario(\EC\PrincipalBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios[] = $usuarios;
    
        return $this;
    }

    /**
     * Remove usuarios
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $usuarios
     */
    public function removeUsuario(\EC\PrincipalBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios->removeElement($usuarios);
    }

    /**
     * Get usuarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }
}