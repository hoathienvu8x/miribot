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
        $node = $this->match($this->graph, $query);

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
        if ($node->countChildren() == 0) {
            if ($node->getTemplate()) {
                return $node;
            } else {
                return false;
            }
        } else {
            // Get the first word of the query
            $word = array_shift($query);
            $matchingTokens = array("#", "_", $word, "^", "*");
            //print_r($word . "|" . $node->__toString() . ' --> ' . implode("|", $query) . "\n");
            foreach($matchingTokens as $token) {
                if ($childNode = $node->getChild(md5($token))) {
                    if ($matchNode = $this->match($childNode, $query)) {
                        return $matchNode;
                    }
                }
            }
            return false;
        }
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
