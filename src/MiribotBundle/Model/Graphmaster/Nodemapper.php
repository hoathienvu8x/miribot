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
     * @var \DOMElement|null
     */
    protected $template = null;

    /**
     * @var array
     */
    protected $children = array();

    /**
     * For storing emotion and stuffs
     * @var array
     */
    protected $extraData = array();

    /**
     * Nodemapper constructor.
     * @param $word
     * @param $pattern
     * @param \DOMElement $template
     */
    public function __construct($word, $pattern, $template)
    {
        $this->id = md5($pattern) . "_" . $word;
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
        return $this->pattern;
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
     * @return null|\DOMElement
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
     * Get extra data
     * @param $key
     * @return array|bool
     */
    public function getExtraData($key)
    {
        return isset($this->extraData[$key]) ? $this->extraData[$key] : false;
    }

    /**
     * Set extra data
     * @param $key
     * @param $value
     * @return $this
     */
    public function setExtraData($key, $value)
    {
        $this->extraData[$key] = $value;
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

        $child->parentId = $this->id;
        $this->children[$child->getId()] = $child;
        return $this;
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
     * @param $word
     * @return array
     */
    public function getChildrenByWord($word)
    {
        return array_filter($this->children, function (Nodemapper $child) use ($word) {
            return $child->getWord() == $word;
        });
    }

    /**
     * Get first child of a node
     * @return mixed|Nodemapper
     */
    public function getFirstChild()
    {
        $tmp = $this->children;
        return array_shift($tmp);
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