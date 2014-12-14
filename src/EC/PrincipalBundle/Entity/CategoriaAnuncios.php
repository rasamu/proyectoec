<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\CategoriaAnuncios
*
* @ORM\Entity
* @ORM\Table(name="categorias_anuncios")
*/
class CategoriaAnuncios
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
    * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Anuncio", mappedBy="categoria")
    */
    protected $anuncios;

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
	 * @return Categoria
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
        $this->anuncios = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     * @return CategoriaAnuncios
     */
    public function addAnuncio(\EC\PrincipalBundle\Entity\Anuncio $anuncios)
    {
        $this->anuncios[] = $anuncios;
    
        return $this;
    }

    /**
     * Remove anuncios
     *
     * @param \EC\PrincipalBundle\Entity\Anuncio $anuncios
     */
    public function removeAnuncio(\EC\PrincipalBundle\Entity\Anuncio $anuncios)
    {
        $this->anuncios->removeElement($anuncios);
    }

    /**
     * Get anuncios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnuncios()
    {
        return $this->anuncios;
    }
}