<?php

namespace EC\ComunidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="EC\ComunidadBundle\Entity\BloqueRepository")
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
     * @ORM\ManyToOne(targetEntity="EC\ComunidadBundle\Entity\Comunidad", inversedBy="bloques")
     * @ORM\JoinColumn(name="id_comunidad", referencedColumnName="id")
     */
    protected $comunidad;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PropietarioBundle\Entity\Propiedad", mappedBy="bloque")
     */
    protected $propiedades;
    
 
    public function __construct()
    {
        $this->propiedades = new ArrayCollection();
    }
    
    /**
     * Set comunidad
     *
     * @param \EC\ComunidadBundle\Entity\Comunidad $comunidad
     * @return Propietario
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
     * Add propiedades
     *
     * @param \EC\PropietarioBundle\Entity\Propiedad $propiedad
     * @return Bloque
     */
    public function addPropiedade(\EC\PropietarioBundle\Entity\Propiedad $propiedades)
    {
        $this->propiedades[] = $propiedades;
    
        return $this;
    }

    /**
     * Remove propiedades
     *
     * @param \EC\PropietarioBundle\Entity\Propiedad $propiedad
     */
    public function removePropiedade(\EC\PropietarioBundle\Entity\Propiedad $propiedades)
    {
        $this->propiedades->removeElement($propiedades);
    }

    /**
     * Get propiedades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPropiedades()
    {
        return $this->propiedades;
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
}