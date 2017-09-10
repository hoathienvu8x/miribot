<?php

namespace BrainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Aiml
 */
class Aiml
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $that;

    /**
     * @var string
     */
    private $topic;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $fileName;

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
     * @param $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set pattern
     *
     * @param string $pattern
     * @return Aiml
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set that
     *
     * @param string $that
     * @return Aiml
     */
    public function setThat($that)
    {
        $this->that = $that;

        return $this;
    }

    /**
     * Get that
     *
     * @return string
     */
    public function getThat()
    {
        return $this->that;
    }

    /**
     * Set topic
     *
     * @param string $topic
     * @return Aiml
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return Aiml
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
