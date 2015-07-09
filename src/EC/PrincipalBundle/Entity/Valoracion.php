<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* EC\PrincipalBundle\Entity\Valoracion
*
* @ORM\Entity
* @ORM\Table(name="Valoraciones")
* @ORM\HasLifecycleCallbacks
*/
class Valoracion
{
	/**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\AdminFincas", inversedBy="valoraciones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="adminfincas_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $adminfincas;
 
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Servicio", inversedBy="valoraciones")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="servicio_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $servicio;
    
    /**
	  * @var string $mensaje
	  * @Assert\NotNull()
	  * @Assert\Type(type="string")
	  * @ORM\Column(name="mensaje", type="string", length=500)
	  */
    protected $mensaje;
    
    /**
     * @ORM\Column(name="puntuacion",type="integer",length=1)
     */
    protected $puntuacion;
    
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
     * Set mensaje
     *
     * @param string $mensaje
     * @return Valoracion
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
     * Set puntuacion
     *
     * @param integer $puntuacion
     * @return Valoracion
     */
    public function setPuntuacion($puntuacion)
    {
        $this->puntuacion = $puntuacion;
    
        return $this;
    }

    /**
     * Get puntuacion
     *
     * @return integer 
     */
    public function getPuntuacion()
    {
        return $this->puntuacion;
    }

    /**
     * Set adminfincas
     *
     * @param \EC\PrincipalBundle\Entity\AdminFincas $adminfincas
     * @return Valoracion
     */
    public function setAdminfincas(\EC\PrincipalBundle\Entity\AdminFincas $adminfincas)
    {
        $this->adminfincas = $adminfincas;
    
        return $this;
    }

    /**
     * Get adminfincas
     *
     * @return \EC\PrincipalBundle\Entity\AdminFincas 
     */
    public function getAdminfincas()
    {
        return $this->adminfincas;
    }

    /**
     * Set servicio
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicio
     * @return Valoracion
     */
    public function setServicio(\EC\PrincipalBundle\Entity\Servicio $servicio)
    {
        $this->servicio = $servicio;
    
        return $this;
    }

    /**
     * Get servicio
     *
     * @return \EC\PrincipalBundle\Entity\Servicio 
     */
    public function getServicio()
    {
        return $this->servicio;
    }
}