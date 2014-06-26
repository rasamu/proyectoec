<?php
namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use \DateTime;

/**
 * EC\PrincipalBundle\Entity\Reunion
 *
 * @ORM\Entity
 * @ORM\Table(name="Reuniones")
 */
class Reunion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
	* @var string $asunto
	* @Assert\NotNull()
	* @Assert\Type(type="string")
	* @ORM\Column(name="descripcion", type="string")
	*/
    protected $descripcion;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Comunidad", inversedBy="reuniones")
     * @ORM\JoinColumn(name="comunidad", referencedColumnName="id")
     */
    protected $comunidad;

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
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }
    
    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Reunion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Documento
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
     * Set comunidad
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidad
     * @return Documento
     */
    public function setComunidad(\EC\PrincipalBundle\Entity\Comunidad $comunidad)
    {
        $this->comunidad = $comunidad;
    
        return $this;
    }

    /**
     * Get comunidad
     *
     * @return \EC\PrincipalBundle\Entity\Comunidad 
     */
    public function getComunidad()
    {
        return $this->comunidad;
    }
    
    /**
     * Return Date in ISO8601 format
     *
     * @return String
     */
    public function fecha_toString() {
        return $this->getFecha()->format('Y-m-d H:i');
    }
}