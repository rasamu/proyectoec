<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* EC\PrincipalBundle\Entity\ConsultaIncidencia
*
* @ORM\Entity
* @ORM\Table(name="consultas_incidencias")
* @ORM\HasLifecycleCallbacks
*/
class ConsultaIncidencia
{
	/**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Usuario", inversedBy="consultas_incidencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $usuario;
 
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Incidencia", inversedBy="consultas_incidencias")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="incidencia_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $incidencia;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
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
     * Set usuario
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $usuario
     * @return Consulta
     */
    public function setUsuario(\EC\PrincipalBundle\Entity\Usuario $usuario)
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
     * Set incidencia
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencia
     * @return Consulta
     */
    public function setIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencia)
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