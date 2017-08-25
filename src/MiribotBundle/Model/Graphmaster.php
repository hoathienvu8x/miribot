<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 13:10
 */

namespace MiribotBundle\Model;


use MiribotBundle\Helper\Helper;
use MiribotBundle\Model\Graphmaster\Nodemapper;
use Symfony\Component\HttpKernel\Kernel;

class Graphmaster
{
    protected $kernel;
    protected $graph;
    protected $aimlPath;
    protected $helper;

    /**
     * Graphmaster constructor.
     * @param Kernel $kernel
     * @param Helper $helper
     */
    public function __construct(Kernel $kernel, Helper $helper)
    {
        $this->kernel = $kernel;
        $this->helper = $helper;
        $this->graph = new Nodemapper('[root]', '[root]', null);
        $this->aimlPath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'aiml';
        $this->build();
    }

    /**
     * Get graph
     * @return Nodemapper
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * Build graph
     * @return $this
     */
    public function build()
    {
        // Fetch AIML data
        $aiml = new \SimpleXMLElement($this->loadAimlData());

        /** @var \SimpleXMLElement $categories */
        $categories = $aiml->category;

        // Map AIML data to bot's Graphmaster knowledge
        $this->mapToNodemapper($categories);

        return $this;
    }

    /**
     * @param $query
     * @return bool|Nodemapper|mixed
     */
    public function matchQueryPattern($query)
    {
        // Find the node that matches query pattern
        $node = $this->match1($this->graph, $query);

        /**
         * If there is no definition for the node then see whether there exists a wildcard node after it
         * that is belongs to 0 or more pattern
         */
        if (!$node || !$node->getTemplate()) {
            return false;
        }

        // Return the real node if there exists srai reference
        if ($srai = $node->getTemplate()->srai) {
            return $this->getReferenceNode($srai);
        }

        return $node;
    }

    /**
     * @param Nodemapper|bool|mixed $node
     * @param $query
     * @return bool|Nodemapper|mixed
     */
    protected function match($node, $query)
    {
        if ($node->getTemplate() !== null) {
            return $node;
        }

        // Get the first word of the query
        $word = array_shift($query);

        /**
         * If the first word has an exact match with node's content
         * then track down the path that leads to goal node recursively
         * with the next word in the query.
         * If no matched goal node is found, place the word back to the
         * head of the query.
         */
        if ($word == $node->getWord()) {
            $nextWord = array_shift($query);
            $nextNode = $node->getChild(md5($nextWord));

            if ($nextNode) {
                return $this->match($nextNode, $query);
            } else {
                array_unshift($query, $nextWord);
            }
        }

        print_r($word . "|" . $node->__toString() . ' --> ' . implode("|", $query) . "\n");

        $matchingTokens = array("#", "_", $word, "^", "*");
        foreach ($matchingTokens as $idx => $token) {
            $tokenNode = $node->getChild(md5($token));

            if ($tokenNode) {

                /**
                 * First word of the query actually belongs to a pattern with any of the
                 * wildcard as prefix
                 * e.g. `# hello` instead of `hello (something)`
                 * then put back the word to the query and search
                 */
                if ($tokenNode->getChild(md5($word))) {
                    array_unshift($query, $word);
                    return $this->match($tokenNode, $query);
                }

                $nextNode = $this->match($tokenNode, $query);

                if ($nextNode) {
                    return $nextNode;
                } else {
                    array_unshift($query, $word);
                }

            }
        }

        return false;

    }

    protected function match1($node, $query)
    {
        if (empty($query) || $node->getTemplate() !== null) {
            return $node;
        }

        $word = array_shift($query);

        print_r($word . "|" . $node->__toString() . ' --> ' . implode("|", $query) . "\n");

        $matchingTokens = array("#", "_", $word, "^", "*");
        foreach ($matchingTokens as $token) {
            if ($node->getWord() == $token) {
                $suffix = array_shift($query);
                foreach($node->getChildren() as $childNode) {
                    if ($matched = $this->match1($childNode, $query)) {
                        return $matched;
                    } else {
                        array_unshift($query, $suffix);
                    }
                }
            } else {
                if ($nextBranch = $node->getChild(md5($word))) {
                    array_unshift($query, $word);
                    return $this->match1($nextBranch, $query);
                }
            }
        }

        return false;
    }

    /**
     * Search for reference node
     * @param \SimpleXMLElement $srai
     * @return bool
     */
    protected function getReferenceNode($srai)
    {
        $sraiTxt = $srai->__toString();

        // Replace <star/> in srai
        if ($srai->star) {
            $sraiTxt = preg_replace("/<star[^>]*\/>/", '*', $sraiTxt);
        }

        return $this->matchQueryPattern($this->tokenize($sraiTxt));
    }

    /**
     * Map AIML data to Graphmaster
     * @param $categories
     */
    protected function mapToNodemapper($categories)
    {
        /** @var \SimpleXMLElement $category */
        foreach ($categories as $category) {

            // Get pattern string
            $pattern = $category->pattern->__toString();

            /** @var \SimpleXMLElement $that */
            if ($that = $category->that) {
                // In case the category contains that, add it to the pattern
                $pattern .= " " . $that->__toString();
            }

            // Build pattern tokens
            $patternTokens = explode(" ", $pattern);

            // Create a category branch that contains word Nodemappers lead to a specific template
            // then add to the knowledge Graphmaster

            /** @var Nodemapper $categoryBranchNode */
            $categoryBranchNode = null;

            for ($i = count($patternTokens) - 1; $i >= 0; $i--) {
                $word = $patternTokens[$i];

                // If the node contains last word in an entry, set response template
                if ($i == count($patternTokens) - 1) {
                    $template = $category->template;
                } else { // Otherwise leave the template null
                    $template = null;
                }

                // Create a new word node
                $wordNode = new Nodemapper($word, $pattern, $template);

                if ($categoryBranchNode) {
                    $wordNode->addChild($categoryBranchNode);
                }

                $categoryBranchNode = $wordNode;
            }

            $this->graph->addChild($categoryBranchNode);
        }
    }

    /**
     * Load AIML data to memory
     * @return string
     */
    protected function loadAimlData()
    {
        // Initialize AIML header
        $aimlString = "<?xml version = \"1.0\" encoding = \"UTF-8\"?>\n<aiml version=\"2.0\" encoding=\"UTF-8\">\n";

        // Read and merge all AIML file contents into one raw XML string
        foreach (glob($this->aimlPath . DIRECTORY_SEPARATOR . "*.aiml") as $aimlFile) {
            $fileContent = strip_tags(file_get_contents($aimlFile), $this->getAimlTagList());
            $aimlString .= trim($fileContent) . "\n";
        }

        $aimlString .= "</aiml>";

        return $aimlString;
    }

    /**
     * Obtain allowed AIML tags
     * @return string
     */
    protected function getAimlTagList()
    {
        $tagList = array(
            '<bot>',
            '<category>',
            '<condition>',
            '<date>',
            '<denormalize>',
            '<eval>',
            '<explode>',
            '<first>',
            '<formal>',
            '<gender>',
            '<get>',
            '<id>',
            '<input>',
            '<interval>',
            '<learn>',
            '<li>',
            '<loop>',
            '<lowercase>',
            '<map>',
            '<normalize>',
            '<pattern>',
            '<person>',
            '<person2>',
            '<program>',
            '<random>',
            '<request>',
            '<response>',
            '<rest>',
            '<sentence>',
            '<set>',
            '<size>',
            '<sr>',
            '<srai>',
            '<sraix>',
            '<star>',
            '<template>',
            '<that>',
            '<thatstar>',
            '<think>',
            '<topic>',
            '<topicstar>',
            '<uppercase>',
        );

        return implode("", $tagList);
    }

    /**
     * Get string tokens
     * @param $text
     * @return array
     */
    protected function tokenize($text)
    {
        $tokens = mb_split(' ', $text);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }
}
