<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Servicios")
 * @ORM\HasLifecycleCallbacks
 */
class Servicio
{	
	/**
     * @ORM\Id
     * @ORM\Column(name="id",type="integer",unique=true,length=9)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
	  * @Assert\Length(
     *      min=9,
     *      max=9
     * )
     * @Assert\Type(type="string")
     * @ORM\Column(name="cif",type="string",unique=true,length=9)
     */
    protected $cif;
    
    /**
	  * @Assert\NotNull()
     * @ORM\Column(name="nombre",type="string", length=100)
     */ 
    protected $nombre;
    
    /**
     * @ORM\Column(name="telefono",type="string",length=9)
     */
    protected $telefono;
     
    /**
	  * @Assert\NotNull()
	  * @Assert\Type(type="string")
     * @ORM\Column(name="direccion_bloque",type="string", length=155)
     */
    protected $direccion;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\City", inversedBy="servicios")
     * @ORM\JoinColumn(name="city", referencedColumnName="id")
     */
    protected $city;
    
    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\CategoriaServicios", inversedBy="servicios")
     * @ORM\JoinColumn(name="categoria", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $categoria;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Valoracion", mappedBy="servicio", cascade={"remove"})
     */
    protected $valoraciones;
    
    /**
     * @ORM\ManyToMany(targetEntity="EC\PrincipalBundle\Entity\Comunidad", mappedBy="servicios")
     */
    protected $comunidades;

    public function __construct() {
        $this->comunidades = new ArrayCollection();
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
     * Set cif
     *
     * @param string $cif
     * @return Servicio
     */
    public function setCif($cif)
    {
        $this->cif = $cif;
    
        return $this;
    }

    /**
     * Get cif
     *
     * @return string 
     */
    public function getCif()
    {
        return $this->cif;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Servicio
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
     * Set direccion
     *
     * @param string $direccion
     * @return Servicio
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Add comunidades
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidades
     * @return Servicio
     */
    public function addComunidade(\EC\PrincipalBundle\Entity\Comunidad $comunidades)
    {
        $this->comunidades[] = $comunidades;
    
        return $this;
    }

    /**
     * Remove comunidades
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidades
     */
    public function removeComunidade(\EC\PrincipalBundle\Entity\Comunidad $comunidades)
    {
        $this->comunidades->removeElement($comunidades);
    }

    /**
     * Get comunidades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComunidades()
    {
        return $this->comunidades;
    }

    /**
     * Set city
     *
     * @param \EC\PrincipalBundle\Entity\City $city
     * @return Servicio
     */
    public function setCity(\EC\PrincipalBundle\Entity\City $city = null)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return \EC\PrincipalBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Servicio
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set categoria
     *
     * @param \EC\PrincipalBundle\Entity\CategoriaServicios $categoria
     * @return Servicio
     */
    public function setCategoria(\EC\PrincipalBundle\Entity\CategoriaServicios $categoria = null)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return \EC\PrincipalBundle\Entity\CategoriaServicios 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Add valoraciones
     *
     * @param \EC\PrincipalBundle\Entity\Valoracion $valoraciones
     * @return Servicio
     */
    public function addValoracione(\EC\PrincipalBundle\Entity\Valoracion $valoraciones)
    {
        $this->valoraciones[] = $valoraciones;
    
        return $this;
    }

    /**
     * Remove valoraciones
     *
     * @param \EC\PrincipalBundle\Entity\Valoracion $valoraciones
     */
    public function removeValoracione(\EC\PrincipalBundle\Entity\Valoracion $valoraciones)
    {
        $this->valoraciones->removeElement($valoraciones);
    }

    /**
     * Get valoraciones
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValoraciones()
    {
        return $this->valoraciones;
    }
}