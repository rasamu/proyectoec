<?php
namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
	  * @var string $asunto
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
	  * @var string $asunto
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
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $path_1;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $path_2;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $path_3;
    
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
     * @Assert\File(
     *	maxSize="1000000",
     *	maxSizeMessage = "El fichero ocupa demasiado.",
     *	notFoundMessage = "Seleccione una imagen.",
     *   mimeTypes = {"image/jpeg"},
     *   mimeTypesMessage = "Por favor, suba un imagen JPG válida."
     * )
     */
    private $file_1;
    
    /**
     * @Assert\File(
     *	maxSize="1000000",
     *	maxSizeMessage = "El fichero ocupa demasiado.",
     *	notFoundMessage = "Seleccione una imagen.",
     *   mimeTypes = {"image/jpeg"},
     *   mimeTypesMessage = "Por favor, suba un imagen JPG válida."
     * )
     */
    private $file_2;
    
    /**
     * @Assert\File(
     *	maxSize="1000000",
     *	maxSizeMessage = "El fichero ocupa demasiado.",
     *	notFoundMessage = "Seleccione una imagen.",
     *   mimeTypes = {"image/jpeg"},
     *   mimeTypesMessage = "Por favor, suba un imagen JPG válida."
     * )
     */
    private $file_3;
    
    private $temp_1;
    
    private $temp_2;
        
    private $temp_3;
    
    /**
     * Get file_1.
     *
     * @return UploadedFile
     */
    public function getFile1()
    {
        return $this->file_1;
    }
    
    /**
     * Get file_2.
     *
     * @return UploadedFile
     */
    public function getFile2()
    {
        return $this->file_2;
    }
    
    /**
     * Get file_3.
     *
     * @return UploadedFile
     */
    public function getFile3()
    {
        return $this->file_3;
    }

    /**
     * Sets file_1.
     *
     * @param UploadedFile $file
     */
    public function setFile1(UploadedFile $file = null)
    {
        $this->file_1 = $file;
        // check if we have an old image path
        if (isset($this->path_1)) {
            // store the old name to delete after the update
            $this->temp_1 = $this->path_1;
            $this->path_1 = null;
        } else {
            $this->path_1 = 'initial';
        }
    }
    
    /**
     * Sets file_2.
     *
     * @param UploadedFile $file
     */
    public function setFile2(UploadedFile $file = null)
    {
        $this->file_2 = $file;
        // check if we have an old image path
        if (isset($this->path_2)) {
            // store the old name to delete after the update
            $this->temp_2 = $this->path_2;
            $this->path_2 = null;
        } else {
            $this->path_2 = 'initial';
        }
    }
    
    /**
     * Sets file_3.
     *
     * @param UploadedFile $file
     */
    public function setFile3(UploadedFile $file = null)
    {
        $this->file_3 = $file;
        // check if we have an old image path
        if (isset($this->path_3)) {
            // store the old name to delete after the update
            $this->temp_3 = $this->path_3;
            $this->path_3 = null;
        } else {
            $this->path_3 = 'initial';
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile1()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path_1 = $filename.'.'.$this->getFile1()->guessExtension();
        }
        if (null !== $this->getFile2()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path_2 = $filename.'.'.$this->getFile2()->guessExtension();
        }
        if (null !== $this->getFile3()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path_3 = $filename.'.'.$this->getFile3()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload1()
    {
        if (null === $this->getFile1()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile1()->move($this->getUploadRootDir(), $this->path_1);

        // check if we have an old image
        if (isset($this->temp_1)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp_1);
            // clear the temp image path
            $this->temp_1 = null;
        }
        $this->file_1 = null;
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload2()
    {
        if (null === $this->getFile2()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile2()->move($this->getUploadRootDir(), $this->path_2);

        // check if we have an old image
        if (isset($this->temp_2)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp_2);
            // clear the temp image path
            $this->temp_2 = null;
        }
        $this->file_2 = null;
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload3()
    {
        if (null === $this->getFile3()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile3()->move($this->getUploadRootDir(), $this->path_3);

        // check if we have an old image
        if (isset($this->temp_3)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp_3);
            // clear the temp image path
            $this->temp_3 = null;
        }
        $this->file_3 = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload1()
    {
        if ($file_1 = $this->getAbsolutePath1()) {
            unlink($file_1);
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload2()
    {
        if ($file_2 = $this->getAbsolutePath2()) {
            unlink($file_2);
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload3()
    {
        if ($file_3 = $this->getAbsolutePath3()) {
            unlink($file_3);
        }
    }

    public function getAbsolutePath1()
    {
        return null === $this->path_1
            ? null
            : $this->getUploadRootDir().'/'.$this->path_1;
    }
    
    public function getAbsolutePath2()
    {
        return null === $this->path_2
            ? null
            : $this->getUploadRootDir().'/'.$this->path_2;
    }
    
    public function getAbsolutePath3()
    {
        return null === $this->path_3
            ? null
            : $this->getUploadRootDir().'/'.$this->path_3;
    }

    public function getWebPath1()
    {
        return null === $this->path_1
            ? null
            : $this->getUploadDir().'/'.$this->path_1;
    }
    
    public function getWebPath2()
    {
        return null === $this->path_2
            ? null
            : $this->getUploadDir().'/'.$this->path_2;
    }
    
    public function getWebPath3()
    {
        return null === $this->path_3
            ? null
            : $this->getUploadDir().'/'.$this->path_3;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/anuncios';
    }
    
    public function getFormat1(){
    		return pathinfo($this->path_1, PATHINFO_EXTENSION);
    }
    
    public function getFormat2(){
    		return pathinfo($this->path_2, PATHINFO_EXTENSION);
    }
    
    public function getFormat3(){
    		return pathinfo($this->path_3, PATHINFO_EXTENSION);
    }
    
    public function getSize1(){
    	   $a = array("B", "KB", "MB", "GB", "TB", "PB");
         $pos = 0;
         $size = filesize($this->getWebPath1());
         while ($size >= 1024) {
                $size /= 1024;
                $pos++;
         }
         return round($size,2)." ".$a[$pos];
    }
    
    public function getSize2(){
    	   $a = array("B", "KB", "MB", "GB", "TB", "PB");
         $pos = 0;
         $size = filesize($this->getWebPath2());
         while ($size >= 1024) {
                $size /= 1024;
                $pos++;
         }
         return round($size,2)." ".$a[$pos];
    }
    
    public function getSize3(){
    	   $a = array("B", "KB", "MB", "GB", "TB", "PB");
         $pos = 0;
         $size = filesize($this->getWebPath3());
         while ($size >= 1024) {
                $size /= 1024;
                $pos++;
         }
         return round($size,2)." ".$a[$pos];
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
     * Set path_1
     *
     * @param string $path1
     * @return Anuncio
     */
    public function setPath1($path1)
    {
        $this->path_1 = $path1;
    
        return $this;
    }

    /**
     * Get path_1
     *
     * @return string 
     */
    public function getPath1()
    {
        return $this->path_1;
    }

    /**
     * Set path_2
     *
     * @param string $path2
     * @return Anuncio
     */
    public function setPath2($path2)
    {
        $this->path_2 = $path2;
    
        return $this;
    }

    /**
     * Get path_2
     *
     * @return string 
     */
    public function getPath2()
    {
        return $this->path_2;
    }

    /**
     * Set path_3
     *
     * @param string $path3
     * @return Anuncio
     */
    public function setPath3($path3)
    {
        $this->path_3 = $path3;
    
        return $this;
    }

    /**
     * Get path_3
     *
     * @return string 
     */
    public function getPath3()
    {
        return $this->path_3;
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
}