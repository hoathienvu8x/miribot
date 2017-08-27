<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:20
 */

namespace MiribotBundle\Helper;

use MiribotBundle\Model\Graphmaster\Nodemapper;
use Symfony\Component\HttpKernel\Kernel;

class TemplateHelper
{
    /**
     * @var MemoryHelper
     */
    protected $memory;

    /**
     * @var StringHelper
     */
    protected $string;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * TemplateHelper constructor.
     * @param Kernel $kernel
     * @param MemoryHelper $memory
     */
    public function __construct(Kernel $kernel, MemoryHelper $memory, StringHelper $string)
    {
        $this->memory = $memory;
        $this->kernel = $kernel;
        $this->string = $string;
    }

    /**
     * Process template logics
     * @param Nodemapper $node
     * @param array $referenceNodes
     * @param array $userInputTokens
     */
    public function processNodeTemplate(&$node, $referenceNodes, $userInputTokens)
    {
        // Retrieve node's template
        $template = $node->getTemplate();

        // Process template data
        $this->handleRandomResponse($template)// Select random response if necessary
        ->handleWildcards($template, $node, $userInputTokens)// Replace all template wildcards with user input
        ->handleSetters($template)// Handle set tags
        ->handleThinks($template)// Handle think tags
        ->handleGetters($template)// Handle get tags
        ->handleConditions($template)// Process conditional tags
        ->handleReferences($template, $referenceNodes)// Get all references
        ->handleEmotions($node, $template)// Handle emotion tags
        ->handleMapData($template)// Handle map tags
        ->handleBotData($template)// Handle bot tags
        ->handleLearning($template);// Learn from template

        // Set the processed template back to the node
        $node->setTemplate($template);
    }

    /**
     * @param \DOMElement $template
     * @param array $referenceNodes
     * @return $this
     */
    public function handleReferences(&$template, $referenceNodes)
    {
        $srais = $template->getElementsByTagName("srai");
        $noOfSrais = $srais->length;

        for ($i = 0; $i < $noOfSrais; $i++) {
            $srai = $srais->item(0);
            if (isset($referenceNodes[$i]) && $referenceNodes[$i]) {
                $ref = $srai->ownerDocument->createTextNode($referenceNodes[$i]->getTemplate()->textContent);
                $srai->parentNode->replaceChild($ref, $srai);
            }
        }

        return $this;
    }

    /**
     * Process condition tags
     * @param \DOMElement $template
     * @return $this
     */
    public function handleConditions(&$template)
    {
        /** @var \DOMElement $condition */
        if ($condition = $template->getElementsByTagName("condition")->item(0)) {
            $variableName = $condition->getAttribute("name");
            $variableData = $this->memory->recallUserData("variables.{$variableName}");
            $variableData = $this->string->substituteWords($variableData);

            $default = $template;
            $matched = false;

            if ($lis = $condition->getElementsByTagName("li")) {
                /** @var \DOMElement $li */
                foreach ($lis as $li) {
                    if (!$li->hasAttribute("value")) {
                        $default = $li;
                    } else {
                        $val = $li->getAttribute("value");
                        if (mb_strpos($variableData, $val) !== FALSE) {
                            $template = $li;
                            $matched = true;
                            break;
                        }
                    }
                }
            }

            // If no response template matched then fallback to default
            if (!$matched) {
                $template = $default;
            }
        }

        return $this;
    }

    /**
     * Get random template response
     * @param \DOMElement $template
     * @return $this
     */
    public function handleRandomResponse(&$template)
    {
        /** @var \DOMElement $random */
        if ($random = $template->getElementsByTagName("random")->item(0)) {
            $lis = $random->getElementsByTagName("li");

            // Get maximum response index
            $maxIdx = $lis->length - 1;

            // Randomize response content from min to max index
            $idx = mt_rand(0, $maxIdx);

            $template = $lis->item($idx);
        }
        return $this;
    }

    /**
     * @param array $pattern
     * @param array $userInputTokens
     * @return array
     */
    public function extractWildcardData($pattern, $userInputTokens)
    {
        $wildcards = array("#", "_", "^", "*");

        $results = array();
        $collect = false;
        $words = "";

        $patternTokenId = 0;
        foreach ($userInputTokens as $id => $token) {
            $patternToken = isset($pattern[$patternTokenId]) ? $pattern[$patternTokenId] : "";
            $nextPatternToken = isset($pattern[$patternTokenId + 1]) ? $pattern[$patternTokenId + 1] : "";


            if (in_array($patternToken, $wildcards)) {
                $collect = true;
                if ($this->string->stringcmp($nextPatternToken, $token) == 0) {
                    $collect = false;

                    if (!empty($words)) {
                        $word = trim($words);
                        $results[] = array(
                            "original" => $word,
                            "replaced" => $this->string->substituteWords($word)
                        );
                        $words = "";
                    }

                    $patternTokenId++;
                }
            } else {
                $patternTokenId++;
            }

            if ($collect || (!empty($results) && in_array($nextPatternToken, $wildcards))) {
                $words .= $token . " ";
            }

            if (($id == count($userInputTokens) - 1) && !empty($words)) {
                $word = trim($words);
                $results[] = array(
                    "original" => $word,
                    "replaced" => $this->string->substituteWords($word)
                );
            }
        }

        return $results;
    }

    /**
     * Replace all wildcards in template with user input values
     * @param \DOMElement $template
     * @param Nodemapper $node
     * @param array $userInputTokens
     * @return $this
     */
    public function handleWildcards(&$template, $node, $userInputTokens)
    {
        // Extract wildcard data
        $wildcardData = $this->extractWildcardData(explode(" ", $node->getPattern()), $userInputTokens);

        $stars = $template->getElementsByTagName("star");
        $noOfStars = $stars->length;
        for ($i = 0; $i < $noOfStars; $i++) {
            $star = $stars->item(0);
            $index = $star->getAttribute("index");

            if (empty($index)) {
                $index = 0;
            } else {
                $index = intval($index);
            }

            if (isset($wildcardData[$index])) {
                if ($star->parentNode->tagName == "map") {
                    $wildcardValue = $wildcardData[$index]["replaced"];
                } else {
                    $wildcardValue = $wildcardData[$index]["original"];
                }
                $wildcardNode = $template->ownerDocument->createTextNode($wildcardValue);
            } else {
                $wildcardNode = $template->ownerDocument->createTextNode("");
            }

            $star->parentNode->replaceChild($wildcardNode, $star);

        }

        return $this;
    }

    /**
     * Handle getter tags
     * @param \DOMElement $template
     * @return $this
     */
    public function handleGetters(&$template)
    {
        $getters = $template->getElementsByTagName("get");
        $noOfGetters = $getters->length;
        for ($i = 0; $i < $noOfGetters; $i++) {
            $getter = $getters->item(0);
            $variableName = $getter->getAttribute("name");
            $variableData = $this->memory->recallUserData("variables.{$variableName}");
            $replacement = $template->ownerDocument->createTextNode($variableData);
            $getter->parentNode->replaceChild($replacement, $getter);
        }

        return $this;
    }

    /**
     * Handle setter tags
     * @param \DOMElement $template
     * @return $this
     */
    public function handleSetters(&$template)
    {
        if ($template->getElementsByTagName("set")->length > 0) {
            $setters = $template->getElementsByTagName("set");
            $noOfSetters = $setters->length;
            for ($i = 0; $i < $noOfSetters; $i++) {
                $setter = $setters->item(0);
                $variableName = $setter->getAttribute("name");
                $variableData = $setter->textContent;
                if ($variableName == 'topic') { // Save topic in separate memory cache
                    $this->memory->rememberTopic($variableData);
                } else {
                    $this->memory->rememberUserData("variables.{$variableName}", $variableData);
                }
            }
        }

        return $this;
    }

    /**
     * Handle <think> tags
     * @param \DOMElement $template
     * @return $this
     */
    public function handleThinks(&$template)
    {
        if ($template->getElementsByTagName("think")->length > 0) {
            $thinks = $template->getElementsByTagName("think");
            $noOfThinks = $thinks->length;
            for ($i = 0; $i < $noOfThinks; $i++) {
                $think = $thinks->item(0);
                $think->parentNode->removeChild($think);
            }
        }

        return $this;
    }

    /**
     * Handle <emotion> tags
     * @param Nodemapper $node
     * @param \DOMElement $template
     * @return $this;
     */
    public function handleEmotions(Nodemapper &$node, &$template)
    {
        if ($template->getElementsByTagName("emotion")->length > 0) {
            $emotionTag = $template->getElementsByTagName("emotion")->item(0);
            $emotion = $emotionTag->getAttribute("value");
            $node->setExtraData("emotion", $emotion);
        }
        return $this;
    }

    /**
     * @param \DOMElement $template
     * @return $this
     */
    public function handleLearning(&$template)
    {
        // Get learn document
        $learnPath = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'aiml' . DIRECTORY_SEPARATOR . "learn.aiml";

        $learnContent = @file_get_contents($learnPath);
        $learnAiml = new \DOMDocument();

        // If no document for learning, create one
        if (!$learnContent) {
            $aimlString = "<?xml version = \"1.0\" encoding = \"UTF-8\"?>\n<aiml version=\"2.0\" encoding=\"UTF-8\">\n</aiml>";
            $learnAiml->loadXML($aimlString);
        } else {
            $learnAiml->loadXML($learnContent);
        }

        // Append learned data
        if ($template->getElementsByTagName("learn")->length > 0) {
            $learnTag = $template->getElementsByTagName("learn")->item(0);
            $categories = $learnTag->getElementsByTagName("category");
            $this->addToLearnedData($categories, $learnAiml);
            $learnTag->parentNode->removeChild($learnTag);
        }

        $learnAiml->save($learnPath);

        return $this;
    }

    /**
     * Handle <bot> tag
     * @param \DOMElement $template
     * @return $this
     */
    public function handleBotData(&$template)
    {
        if ($template->getElementsByTagName("bot")->length > 0) {
            $botProps = $template->getElementsByTagName("bot");
            $noOfBotProps = $botProps->length;
            for ($i = 0; $i < $noOfBotProps; $i++) {
                $botProp = $botProps->item(0);
                $propName = $botProp->getAttribute("name");
                $propValue = $this->memory->recallUserData("bot.{$propName}");
                $replacement = $template->ownerDocument->createTextNode($propValue);
                $botProp->parentNode->replaceChild($replacement, $botProp);
            }
        }
        return $this;
    }

    /**
     * Process mapping data
     * @param \DOMElement $template
     * @return $this;
     */
    public function handleMapData(&$template)
    {
        if ($template->getElementsByTagName("map")->length > 0) {
            $maps = $template->getElementsByTagName("map");
            $noOfMaps = $maps->length;
            for ($i = 0; $i < $noOfMaps; $i++) {
                $map = $maps->item(0);
                $filename = $map->getAttribute("name");
                $originalValue = $map->textContent;
                if ($filename == "successor") {
                    $originalValue = intval($originalValue);
                    $mappedValue = $originalValue + 1;
                } elseif ($filename == "predecessor") {
                    $originalValue = intval($originalValue);
                    $mappedValue = $originalValue - 1;
                } else {
                    $mappedValue = $this->mapData($originalValue, $filename);
                }
                $mappedNode = $template->ownerDocument->createTextNode($mappedValue);
                $map->parentNode->replaceChild($mappedNode, $map);
            }
        }
        return $this;
    }

    /**
     * Map original value to a value defined in a map file
     * @param $originalValue
     * @param $filename
     * @return mixed
     */
    private function mapData($originalValue, $filename)
    {
        $path = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "map" . DIRECTORY_SEPARATOR . $filename . ".json";
        $content = @file_get_contents($path);
        if ($content && $maps = json_decode($content, true)) {
            $originalValue = mb_strtolower($originalValue);
            $mappedValue = isset($maps[$originalValue]) ? $maps[$originalValue] : $originalValue;
        } else {
            $mappedValue = $originalValue;
        }
        return $mappedValue;
    }

    /**
     * Add new categories to learned data
     * @param \DOMNodeList $categories
     * @param \DOMDocument $learnAiml
     */
    private function addToLearnedData($categories, &$learnAiml)
    {
        if ($categories->length > 0) {
            $noOfCategories = $categories->length;
            for ($i = 0; $i < $noOfCategories; $i++) {
                $category = $categories->item(0);
                $pattern = $category->getElementsByTagName('pattern')->item(0);
                if ($pattern) {
                    $importedNode = $learnAiml->importNode($category, true);
                    if ($oldNode = $this->hasPatternString($learnAiml, $pattern)) {
                        $learnAiml->documentElement->replaceChild($importedNode, $oldNode);
                    } else {
                        $learnAiml->documentElement->appendChild($importedNode);
                    }
                }
            }
        }
    }

    /**
     * Check if an AIML DOM Document already has a pattern
     * @param \DOMDocument $aiml
     * @param \DOMNode $pattern
     * @return bool|\DOMNode
     */
    private function hasPatternString($aiml, $pattern)
    {
        $patterns = $aiml->getElementsByTagName('pattern');
        $nPatterns = $patterns->length;

        for ($i = 0; $i < $nPatterns; $i++) {
            $aimlPattern = $patterns->item(0);
            if ($this->string->stringcmp($aimlPattern->textContent, $pattern->textContent) == 0) {
                return $aimlPattern->parentNode;
            }
        }
        return false;
    }
}
