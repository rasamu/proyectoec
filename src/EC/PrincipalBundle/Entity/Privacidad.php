<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
* EC\PrincipalBundle\Entity\Privacidad
*
* @ORM\Entity
* @ORM\Table(name="privacidades")
*/
class Privacidad
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
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Incidencia", mappedBy="privacidad")
     */
    protected $incidencias;

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
	 * @return Estado
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
     * Constructor
     */
    public function __construct()
    {
        $this->incidencias = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add incidencias
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencias
     * @return Privacidad
     */
    public function addIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencias)
    {
        $this->incidencias[] = $incidencias;
    
        return $this;
    }

    /**
     * Remove incidencias
     *
     * @param \EC\PrincipalBundle\Entity\Incidencia $incidencias
     */
    public function removeIncidencia(\EC\PrincipalBundle\Entity\Incidencia $incidencias)
    {
        $this->incidencias->removeElement($incidencias);
    }

    /**
     * Get incidencias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncidencias()
    {
        return $this->incidencias;
    }
}