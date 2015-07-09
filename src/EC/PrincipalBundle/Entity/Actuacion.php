<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* EC\PrincipalBundle\Entity\Actuacion
*
* @ORM\Entity
* @ORM\Table(name="Actuaciones")
* @ORM\HasLifecycleCallbacks
*/
class Actuacion
{
	/**
	*
	* @ORM\Column(name="id", type="integer",unique=true)
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
    protected $id;

  /**
	* @var string $mensaje
	* @Assert\NotNull()
	* @Assert\Type(type="string")
	* @ORM\Column(name="mensaje", type="string", length=500)
	*/
    protected $mensaje;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Incidencia", inversedBy="actuaciones")
     * @ORM\JoinColumn(name="incidencia_id", referencedColumnName="id", nullable=false)
     */
    protected $incidencia;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Usuario", inversedBy="actuaciones")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=true)
     */
    protected $usuario;

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
	 * Set mensaje
	 *
	 * @param string $mensaje
	 * @return Incidencia
	 */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;

        return $this;
    }

	/**
	 * Get mensaje
	 *
	 * @return string
	 */
    public function getMensaje()
    {
        return $this->mensaje;
    }


    /**
     * Set usuario
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $usuario
     * @return Incidencia
     */
    public function setUsuario(\EC\PrincipalBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \EC\PrincipalBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
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
     * Set incidencia
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencia
     * @return Actuacion
     */
    public function setIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencia = null)
    {
        $this->incidencia = $incidencia;
    
        return $this;
    }

    /**
     * Get incidencia
     *
     * @return \EC\PrincipalBundle\Entity\Incidencia 
     */
    public function getIncidencia()
    {
        return $this->incidencia;
    }
}