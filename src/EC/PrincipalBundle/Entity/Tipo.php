<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* EC\PrincipalBundle\Entity\Tipo
*
* @ORM\Entity
* @ORM\Table(name="tipos_documento")
*/
class Tipo
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
     * @ORM\OneToMany(targetEntity="EC\PrincipalBundle\Entity\Documento", mappedBy="tipo")
     */
    protected $documentos;

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
        $this->documentos = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add documentos
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documentos
     * @return Tipo
     */
    public function addIncidencia(\EC\PrincipalBundle\Entity\Documento $documentos)
    {
        $this->documentos[] = $documentos;
    
        return $this;
    }

    /**
     * Remove documentos
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documentos
     */
    public function removeDocumento(\EC\PrincipalBundle\Entity\Documento $documentos)
    {
        $this->documentos->removeElement($documentos);
    }

    /**
     * Get documentos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocumentos()
    {
        return $this->documentos;
    }

    /**
     * Add documentos
     *
     * @param \EC\PrincipalBundle\Entity\Documento $documentos
     * @return Tipo
     */
    public function addDocumento(\EC\PrincipalBundle\Entity\Documento $documentos)
    {
        $this->documentos[] = $documentos;
    
        return $this;
    }
}