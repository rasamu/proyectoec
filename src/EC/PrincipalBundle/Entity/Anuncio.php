<?php
namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * EC\PrincipalBundle\Entity\Anuncio
 *
 * @ORM\Entity
 * @ORM\Table(name="Anuncios")
 * @ORM\HasLifecycleCallbacks
 */
class Anuncio
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
	  * @var string $titulo
	  * @Assert\NotNull()
	  * @Assert\Type(type="string")
	  * @Assert\Length(
     *      min=5,
     *      max=50
     * )
	  * @ORM\Column(name="titulo", type="string", length=50)
	  */
    protected $titulo;
    
    /**
	  * @var string $descripcion
	  * @Assert\NotNull()
	  * @Assert\Type(type="string")
	  * @Assert\Length(
     *      min=5,
     *      max=255
     * )
	  * @ORM\Column(name="descripcion", type="string", length=255)
	  */
    protected $descripcion;
    
    /**
     * @ORM\Column(name="precio",type="integer",length=8)
     */
    protected $precio;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Comunidad", inversedBy="anuncios")
     * @ORM\JoinColumn(name="comunidad", referencedColumnName="id")
     */
    protected $comunidad;
    
     /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\CategoriaAnuncios", inversedBy="anuncios")
     * @ORM\JoinColumn(name="categoria", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $categoria;
    
    /**
     * @ORM\ManyToOne(targetEntity="EC\PrincipalBundle\Entity\Usuario", inversedBy="anuncios")
     * @ORM\JoinColumn(name="usuario", referencedColumnName="id")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $usuario;
    
    /**
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Imagen", mappedBy="anuncio", cascade={"remove"})
     * @ORM\OrderBy({"orden" = "ASC"})
     */
    protected $imagenes;
 
    public function __construct()
    {
        $this->imagenes = new ArrayCollection();
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
     * Set titulo
     *
     * @param string $titulo
     * @return Anuncio
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    
        return $this;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set categoria
     *
     * @param \EC\PrincipalBundle\Entity\CategoriaAnuncios $categoria
     * @return Anuncio
     */
    public function setCategoria(\EC\PrincipalBundle\Entity\CategoriaAnuncios $categoria = null)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return \EC\PrincipalBundle\Entity\CategoriaAnuncios 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set usuario
     *
     * @param \EC\PrincipalBundle\Entity\Usuario $usuario
     * @return Anuncio
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
     * Set precio
     *
     * @param integer $precio
     * @return Anuncio
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    
        return $this;
    }

    /**
     * Get precio
     *
     * @return integer 
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set comunidad
     *
     * @param \EC\PrincipalBundle\Entity\Comunidad $comunidad
     * @return Anuncio
     */
    public function setComunidad(\EC\PrincipalBundle\Entity\Comunidad $comunidad = null)
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
     * Add imagenes
     *
     * @param \EC\PrincipalBundle\Entity\Imagen $imagenes
     * @return Anuncio
     */
    public function addImagene(\EC\PrincipalBundle\Entity\Imagen $imagenes)
    {
        $this->imagenes[] = $imagenes;
    
        return $this;
    }

    /**
     * Remove imagenes
     *
     * @param \EC\PrincipalBundle\Entity\Imagen $imagenes
     */
    public function removeImagene(\EC\PrincipalBundle\Entity\Imagen $imagenes)
    {
        $this->imagenes->removeElement($imagenes);
    }

    /**
     * Get imagenes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImagenes()
    {
        return $this->imagenes;
    }
}