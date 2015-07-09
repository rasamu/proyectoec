<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
* EC\PrincipalBundle\Entity\Log
*
* @ORM\Entity
* @ORM\Table(name="Logs")
* @ORM\HasLifecycleCallbacks
*/
class Log
{
	/**
	*
	* @ORM\Column(name="id", type="integer",unique=true)
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
    protected $id;

    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\Column(name="ip",type="string")
     */
    protected $ip;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Usuario", inversedBy="logs")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)
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
     * @return Log
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
     * Set ip
     *
     * @param string $ip
     * @return Log
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }
}