<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 09:51
 */

namespace MiribotBundle\Model\Graphmaster;

use Symfony\Component\Config\Definition\Exception\Exception;

class Nodemapper
{
    /**
     * @var string|null
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $parentId = null;

    /**
     * @var string|null
     */
    protected $word = null;

    /** @var string|null */
    protected $pattern = null;

    /**
     * @var \SimpleXMLElement|null
     */
    protected $template = null;

    /**
     * @var array
     */
    protected $children = array();

    /**
     * Nodemapper constructor.
     * @param $word
     * @param $pattern
     * @param $template
     */
    public function __construct($word, $pattern, $template)
    {
        $this->id = md5($word);
        $this->pattern = $pattern;
        $this->word = $word;
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->word;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param $parentId
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param $word
     * @return $this
     */
    public function setWord($word)
    {
        $this->word = $word;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPattern()
    {
        return $this->template;
    }

    /**
     * @param $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return null|\SimpleXMLElement
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param Nodemapper $child
     * @return $this
     */
    public function addChild(Nodemapper $child)
    {
        if (!$child->word) {
            throw new Exception('Nodemapper word cannot be emptied!');
        }

        if (isset($this->children[$child->getId()])) {
            // Merge content of the two children
            $internalChild = $this->children[$child->getId()];
            $this->children[$child->getId()] = $this->merge($internalChild, $child);
            return $this;
        }

        $child->parentId = $this->id;
        $this->children[$child->getId()] = $child;
        return $this;
    }

    /**
     * @param Nodemapper $node1
     * @param Nodemapper $node2
     * @return Nodemapper
     */
    protected function merge(Nodemapper $node1, Nodemapper $node2)
    {
        foreach ($node2->children as $child) {
            $node1->addChild($child);
        }

        // Merge template
        $tempXml = "";

        if ($node1->getTemplate()) {
            $tempXml .= $node1->getTemplate()->asXML();
        }

        if ($node2->getTemplate()) {
            $tempXml .= " " . $node2->getTemplate()->asXML();
        }

        if (!empty($tempXml)) {
            $node1->setTemplate(new \SimpleXMLElement($tempXml));
        }

        return $node1;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeChild($id)
    {
        if (!isset($this->children[$id])) {
            throw new Exception("Nodemapper with ID '{$id}' not exists!");
        }

        unset($this->children[$id]);
        return $this;
    }

    /**
     * @param $id
     * @return mixed|Nodemapper
     */
    public function getChild($id)
    {
        if (!isset($this->children[$id])) {
            return false;
        }

        return $this->children[$id];
    }

    /**
     * Get all possible words from the current
     * Nodemapper
     * @return array
     */
    public function getAllPossibleWords()
    {
        $words = array();

        array_walk($this->children, function (Nodemapper $child) use ($words) {
            $words[] = $child->getWord();
        });

        return $words;
    }

    /**
     * @return mixed
     */
    public function getRandomChild()
    {
        $keys = array_keys($this->children);
        return $this->children[$keys[mt_rand(0, $this->countChildren() - 1)]];
    }

    /**
     * @return int
     */
    public function countChildren()
    {
        return count($this->children);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasChild($id)
    {
        return isset($this->children[$id]);
    }
}