<?php

namespace BrainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * AimlUploader
 */
class AimlUploader
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var File|null
     */
    private $file;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileSize;

    /**
     * @var string
     */
    private $fileMimeType;

    /**
     * @var string
     */
    private $fileOriginalName;

    /**
     * @var \DateTime
     */
    private $updatedAt;


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
     * @param File $file
     * @return $this
     */
    public function setFile(File $file = null)
    {
        $this->file = $file;

        if ($file) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return AimlUploader
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileSize
     *
     * @param string $fileSize
     * @return AimlUploader
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return string 
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set fileMimeType
     *
     * @param string $fileMimeType
     * @return AimlUploader
     */
    public function setFileMimeType($fileMimeType)
    {
        $this->fileMimeType = $fileMimeType;

        return $this;
    }

    /**
     * Get fileMimeType
     *
     * @return string 
     */
    public function getFileMimeType()
    {
        return $this->fileMimeType;
    }

    /**
     * Set fileOriginalName
     *
     * @param string $fileOriginalName
     * @return AimlUploader
     */
    public function setFileOriginalName($fileOriginalName)
    {
        $this->fileOriginalName = $fileOriginalName;

        return $this;
    }

    /**
     * Get fileOriginalName
     *
     * @return string 
     */
    public function getFileOriginalName()
    {
        return $this->fileOriginalName;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return AimlUploader
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
