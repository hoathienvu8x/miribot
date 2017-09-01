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
     * @var null|Nodemapper
     */
    protected $parent = null;

    /**
     * @var string|null
     */
    protected $word = null;

    /**
     * @var string|null
     */
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
     * @return Nodemapper
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = &$parent;
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

        if ($internal = $this->getChild($child->getId())) {
            if ($internal->countChildren() > 0) {
                $this->mergeChildren($child, $internal);
            }
            if ($internal->getTemplate() !== null) {
                $child->setPattern($internal->getPattern());
                $child->setTemplate($internal->getTemplate());
            }
        }

        $child->parent = &$this;
        $this->children[$child->getId()] = $child;
        return $this;
    }

    /**
     * Merge the children of 2 nodes
     * @param Nodemapper $node1
     * @param Nodemapper $node2
     */
    protected function mergeChildren(Nodemapper &$node1, Nodemapper &$node2) {
        $node2Children = $node2->getChildren();

        foreach($node2Children as $child) {
            $node1->addChild($child);
        }
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
            return ($this->stringcmp($child->getWord(), $word) == 0);
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
        $idx = range(0, $this->countChildren() - 1);
        return $this->children[$keys[shuffle($idx)]];
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

    private function stringcmp($str1, $str2, $encoding = null) {
        if (null === $encoding) { $encoding = mb_internal_encoding(); }
        return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
    }
}
