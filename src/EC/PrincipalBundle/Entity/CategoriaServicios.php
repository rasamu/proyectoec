<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\CategoriaServicios
*
* @ORM\Entity
* @ORM\Table(name="categorias_servicios")
*/
class CategoriaServicios
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
	 * @ORM\Column(name="nombre", type="string", length=255)
	 */
    protected $nombre;
    
   /**
    * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Servicio", mappedBy="categoria")
    */
    protected $servicios;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servicios = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nombre
     *
     * @param string $nombre
     * @return CategoriaServicios
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
     * Add servicios
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicios
     * @return CategoriaServicios
     */
    public function addServicio(\EC\PrincipalBundle\Entity\Servicio $servicios)
    {
        $this->servicios[] = $servicios;
    
        return $this;
    }

    /**
     * Remove servicios
     *
     * @param \EC\PrincipalBundle\Entity\Servicio $servicios
     */
    public function removeServicio(\EC\PrincipalBundle\Entity\Servicio $servicios)
    {
        $this->servicios->removeElement($servicios);
    }

    /**
     * Get servicios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServicios()
    {
        return $this->servicios;
    }
}