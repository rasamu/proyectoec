<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
* EC\PrincipalBundle\Entity\Incidencia
*
* @ORM\Entity
* @ORM\Table(name="incidencias")
* @ORM\HasLifecycleCallbacks
*/
class Incidencia
{
	/**
	*
	* @ORM\Column(name="id", type="integer",unique=true)
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
    protected $id;

	/**
	* @var string $asunto
	* @Assert\NotNull()
	* @Assert\Type(type="string")
	* @ORM\Column(name="asunto", type="string")
	*/
    protected $asunto;
    
  /**
	* @var string $descripcion
	* @Assert\NotNull()
	* @Assert\Type(type="string")
	* @ORM\Column(name="descripcion", type="text")
	*/
    protected $descripcion;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Propietario", inversedBy="incidencias")
     * @ORM\JoinColumn(name="propietario", referencedColumnName="id")
     */
    protected $propietario;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Estado", inversedBy="incidencias")
     * @ORM\JoinColumn(name="estado", referencedColumnName="id")
     */
    protected $estado;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Privacidad", inversedBy="incidencias")
     * @ORM\JoinColumn(name="privacidad", referencedColumnName="id")
     */
    protected $privacidad;
    
    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Categoria", inversedBy="incidencias")
     * @ORM\JoinColumn(name="categoria", referencedColumnName="id")
     */
    protected $categoria;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Actuacion", mappedBy="incidencia")
     */
    protected $actuaciones;

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
	 * Set descripcion
	 *
	 * @param string $descripcion
	 * @return Incidencia
	 */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

	/**
	 * Get descripcion
	 *
	 * @return string
	 */
    public function getDescripcion()
    {
        return $this->descripcion;
    }


    /**
     * Set propietario
     *
     * @param \EC\PrincipalBundle\Entity\Propietario $propietario
     * @return Incidencia
     */
    public function setPropietario(\EC\PrincipalBundle\Entity\Propietario $propietario = null)
    {
        $this->propietario = $propietario;
    
        return $this;
    }

    /**
     * Get propietario
     *
     * @return \EC\PrincipalBundle\Entity\Propietario
     */
    public function getPropietario()
    {
        return $this->propietario;
    }

    /**
     * Set estado
     *
     * @param \EC\PrincipalBundle\Entity\Estado $estado
     * @return Incidencia
     */
    public function setEstado(\EC\PrincipalBundle\Entity\Estado $estado = null)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return \EC\PrincipalBundle\Entity\Estado 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set categoria
     *
     * @param \EC\PrincipalBundle\Entity\Categoria $categoria
     * @return Incidencia
     */
    public function setCategoria(\EC\PrincipalBundle\Entity\Categoria $categoria = null)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return \EC\PrincipalBundle\Entity\Categoria 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set fecha
	  * @ORM\PrePersist()
     */
    public function setFecha($fecha = null)
    {
        $this->fecha = null === $fecha ? new \DateTime() : $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actuaciones = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add actuaciones
     *
     * @param \EC\PrincipalBundle\Entity\Actuacion $actuaciones
     * @return Incidencia
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
     * Set asunto
     *
     * @param string $asunto
     * @return Incidencia
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;
    
        return $this;
    }

    /**
     * Get asunto
     *
     * @return string 
     */
    public function getAsunto()
    {
        return $this->asunto;
    }

    /**
     * Set privacidad
     *
     * @param \EC\PrincipalBundle\Entity\Privacidad $privacidad
     * @return Incidencia
     */
    public function setPrivacidad(\EC\PrincipalBundle\Entity\Privacidad $privacidad = null)
    {
        $this->privacidad = $privacidad;
    
        return $this;
    }

    /**
     * Get privacidad
     *
     * @return \EC\PrincipalBundle\Entity\Privacidad 
     */
    public function getPrivacidad()
    {
        return $this->privacidad;
    }
}