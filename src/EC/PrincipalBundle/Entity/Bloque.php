<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="EC\PrincipalBundle\Entity\BloqueRepository")
 * @ORM\Table(name="Bloques")
 * @ORM\HasLifecycleCallbacks
 */
class Bloque
{	
	/**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer",unique=true,length=9)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @Assert\Type(type="string")
     * @Assert\NotNull()
     * @ORM\Column(name="num_bloque",type="string",length=9)
     */
    protected $num;
    
    /**
	  * @Assert\NotNull()
	  * @Assert\Type(type="string")
     * @ORM\Column(name="direccion_bloque",type="string", length=155)
     */
    protected $direccion;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Comunidad", inversedBy="bloques")
     * @ORM\JoinColumn(name="id_comunidad", referencedColumnName="id")
     */
    protected $comunidad;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Propietario", mappedBy="bloque", cascade={"remove"})
     */
    protected $propietarios;
    
 
    public function __construct()
    {
        $this->propietarios = new ArrayCollection();
    }
    
    /**
     * Set comunidad
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidad
     * @return Propietario
     */
    public function setComunidad(\EC\PrincipalBundle\Entity\Comunidad $comunidad = null)
    {
        $this->comunidad = $comunidad;
    
        return $this;
    }

    /**
     * Get comunidad
     *
     * @return \EC\PrincipalBundle\Entity\Comunidad 
     */
    public function getComunidad()
    {
        return $this->comunidad;
    }

    /**
     * Set num
     *
     * @param integer $num
     * @return Bloque
     */
    public function setNum($num)
    {
        $this->num = $num;
    
        return $this;
    }

    /**
     * Get num
     *
     * @return integer 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Bloque
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add propietarios
     *
     * @param \EC\PrincipalBundle\Entity\Propietario $propietarios
     * @return Bloque
     */
    public function addPropietario(\EC\PrincipalBundle\Entity\Propietario $propietarios)
    {
        $this->propietarios[] = $propietarios;
    
        return $this;
    }

    /**
     * Remove propietarios
     *
     * @param \EC\PrincipalBundle\Entity\Propietario $propietarios
     */
    public function removePropietario(\EC\PrincipalBundle\Entity\Propietario $propietarios)
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
}