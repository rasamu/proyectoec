<?php
namespace EC\PrincipalBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EC\PrincipalBundle\Entity\Csv
 */
class Csv
{
    /**
     * @Assert\NotBlank
     * @Assert\File(
     *	maxSize = "100k",
     *	maxSizeMessage = "El fichero ocupa demasiado",
     *	notFoundMessage = "Seleccione un fichero",
     *   mimeTypes = {"text/plain", "text/plain"},
     *   mimeTypesMessage = "Por favor, suba un fichero CSV válido"
     * )
     */
    private $file;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
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
}

