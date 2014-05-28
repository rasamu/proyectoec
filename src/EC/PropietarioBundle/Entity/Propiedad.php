<?php

namespace EC\PropietarioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use EC\ComunidadBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Usuario;

/**
 * @ORM\Entity
 * @ORM\Table(name="Propiedades")
 * @ORM\HasLifecycleCallbacks
 */
class Propiedad
{	
	/**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer",unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="piso",type="string", length=155)
     */
    protected $piso;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\ComunidadBundle\Entity\Bloque", inversedBy="propiedades")
     * @ORM\JoinColumn(name="id_bloque", referencedColumnName="id")
     */
    protected $bloque;
    
    /**
     * @ORM\OneToOne(targetEntity="EC\PrincipalBundle\Entity\Usuario", mappedBy="propiedad")
     **/
    private $propietario;

	/**
     * Set piso
     *
     * @param string $piso
     * @return Propiedad
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
     * Set bloque
     *
     * @param \EC\ComunidadBundle\Entity\Bloque $bloque
     * @return Propiedad
     */
    public function setBloque(\EC\ComunidadBundle\Entity\Bloque $bloque = null)
    {
        $this->bloque = $bloque;
    
        return $this;
    }

    /**
     * Get bloque
     *
     * @return \EC\ComunidadBundle\Entity\Bloque 
     */
    public function getBloque()
    {
        return $this->bloque;
    }

    /**
     * Set propietario
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $propietario
     * @return Propiedad
     */
    public function setPropietario(\EC\PrincipalBundle\Entity\Usuario $propietario = null)
    {
        $this->propietario = $propietario;
    
        return $this;
    }

    /**
     * Get propietario
     *
     * @return \EC\PrincipalBundle\Entity\Usuario
     */
    public function getPropietario()
    {
        return $this->propietario;
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