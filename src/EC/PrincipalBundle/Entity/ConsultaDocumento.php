<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* EC\PrincipalBundle\Entity\ConsultaDocumento
*
* @ORM\Entity
* @ORM\Table(name="Consultas_Documentos")
* @ORM\HasLifecycleCallbacks
*/
class ConsultaDocumento
{
	/**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Propietario", inversedBy="consultas_documentos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="propietario_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $propietario;
 
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Documento", inversedBy="consultas_documentos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="documento_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $documento;
    
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
     * Set propietario
     *
     * @param \EC\PrincipalBundle\Entity\Propietario $propietario
     * @return ConsultaDocumento
     */
    public function setPropietario(\EC\PrincipalBundle\Entity\Propietario $propietario)
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
     * Set documento
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documento
     * @return ConsultaDocumento
     */
    public function setDocumento(\EC\PrincipalBundle\Entity\Documento $documento)
    {
        $this->documento = $documento;
    
        return $this;
    }

    /**
     * Get documento
     *
     * @return \EC\PrincipalBundle\Entity\Documento 
     */
    public function getDocumento()
    {
        return $this->documento;
    }
}