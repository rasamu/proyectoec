<?php
namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use \DateTime;

/**
 * EC\PrincipalBundle\Entity\Reunion
 *
 * @ORM\Entity
 * @ORM\Table(name="Reuniones")
 * @ORM\HasLifecycleCallbacks
 */
class Reunion
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
	* @ORM\Column(name="descripcion", type="string")
	*/
    protected $descripcion;
    
    /**
     * @ORM\Column(name="fecha",type="datetime")
     */
    protected $fecha;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $path;
    
    private $temp;
    
    /**
     * @Assert\File(
     *	maxSize="6000000",
     *	maxSizeMessage = "El fichero ocupa demasiado",
     *	notFoundMessage = "Seleccione un fichero",
     *   mimeTypes = {"application/pdf"},
     *   mimeTypesMessage = "Por favor, suba un fichero PDF válido"
     * )
     */
    private $file;
    
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
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
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
        return 'uploads/reuniones';
    }
    
    public function getFormat(){
    		return pathinfo($this->path, PATHINFO_EXTENSION);
    }
    
    public function getSize(){
    	   $a = array("B", "KB", "MB", "GB", "TB", "PB");
         $pos = 0;
         $size = filesize($this->getWebPath());
         while ($size >= 1024) {
                $size /= 1024;
                $pos++;
         }
         return round($size,2)." ".$a[$pos];
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Documento
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }
}