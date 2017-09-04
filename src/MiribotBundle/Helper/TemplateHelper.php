<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:20
 */

namespace MiribotBundle\Helper;

use MiribotBundle\Model\Graphmaster\Nodemapper;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @var Session
     */
    protected $session;

    /**
     * TemplateHelper constructor.
     * @param Kernel $kernel
     * @param MemoryHelper $memory
     * @param StringHelper $string
     * @param Session $session
     */
    public function __construct(Kernel $kernel, Session $session, MemoryHelper $memory, StringHelper $string)
    {
        $this->memory = $memory;
        $this->kernel = $kernel;
        $this->string = $string;
        $this->session = $session;
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
        ->handleUserData($template)// Handle user tags
        ->handleConditions($template)// Process conditional tags
        ->handleReferences($template, $referenceNodes)// Get all references
        ->handleEmotions($node, $template)// Handle emotion tags
        ->handleMapData($template)// Handle map tags
        ->handleBotData($template)// Handle bot tags
        ->handleLearning($template)// Learn from template
        ->handleWikiSearch($template);// Search for info from wikipedia

        // Set the processed template back to the node
        $node->setTemplate($template);
    }

    /**
     * @param \DOMElement $template
     * @return $this
     */
    public function handleUserData(&$template)
    {
        $userTags = $template->getElementsByTagName('user');
        $noOfUserTags = $userTags->length;
        $userData = $this->memory->recallUserData('userinfo');

        for ($i = 0; $i < $noOfUserTags; $i++) {
            $user = $userTags->item(0);
            $userAttrName = $user->getAttribute('name');
            $userAttrValue = isset($userData[$userAttrName]) ? $userData[$userAttrName] : "";
            $userDataNode = $user->ownerDocument->createTextNode($userAttrValue);
            $user->parentNode->replaceChild($userDataNode, $user);
        }

        return $this;
    }

    /**
     * Handle wikipedia search
     * @param \DOMElement $template
     * @return $this
     */
    public function handleWikiSearch(&$template)
    {
        $wikis = $template->getElementsByTagName("wiki");
        $noOfWikis = $wikis->length;

        for ($i = 0; $i < $noOfWikis; $i++) {
            $wiki = $wikis->item(0);
            $keyword = $wiki->textContent;
            $language = $wiki->getAttribute("lang");
            $random = ($this->string->stringcmp($keyword, "randomwiki") == 0);

            if (empty($keyword)) {
                return $this;
            }

            if (empty($language)) {
                $language = "vi";
            }

            if ($info = $this->searchWikipedia($keyword, $language, $random)) {
                $infoNode = $wiki->ownerDocument->createTextNode($info);

                $escKeyword = @mb_eregi_replace("\W", "_", $keyword);

                if (!$escKeyword) {
                    $escKeyword = $keyword;
                }

                $keyword = ucwords($keyword);

                if (!$random) { // If the keyword is not random
                    $linkNode = $wiki->ownerDocument->createElement('a', "(Wikipedia - {$keyword})");
                    $linkNode->setAttribute("href", "https://{$language}.wikipedia.org/wiki/{$escKeyword}");
                    $linkNode->setAttribute("target", "_blank");
                    $wiki->parentNode->appendChild($linkNode);
                }

                $wiki->parentNode->replaceChild($infoNode, $wiki);
            } else {
                $blank = $wiki->ownerDocument->createTextNode("");
                $wiki->parentNode->replaceChild($blank, $wiki);
            }
        }
        return $this;
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
                $ref = $srai->ownerDocument->importNode($referenceNodes[$i]->getTemplate());
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
     * @param array $matchingTokens
     * @return array
     */
    public function extractWildcardData($matchingTokens)
    {
        /**
         * Sample matching tokens data
         * array:14 [
         *       "TRÔNG" => "_"
         *       "MIRI" => ""
         *       "THẬT" => ""
         *       "LÀ" => ""
         *       "KHÔN" => "<set>compliments</set>"
         *       "LẮM" => "*"
         *       "ĐÓ" => ""
         *       "NHA" => ""
         *       "<that>" => "<that>"
         *       "CẢM" => ""
         *       "ƠN" => ""
         *       "ANH" => ""
         *       "Ạ" => ""
         *       "<topic>" => "<topic>"
         *  ]
         */
        $wildcards = ".*[(\#)(\_)(\^)(\*)]";

        $words = array(); // Initialize a set of words that match wildcard
        $prevMatch = "";

        $i = 0;
        foreach ($matchingTokens as $token => $matched) {
            if ($matched === "<that>") { // We won't match content of <that> tag comes after
                break;
            }

            if ($matched == $prevMatch) {
                $i++;
            }

            // Whenever we found a matched wildcard or a set, we would add the first word token to the wildcard's matching token
            if (mb_ereg_match($wildcards, $matched) || strpos($matched, "<set>") !== FALSE) {
                $words[$matched . $i] = $token . " ";
                $prevMatch = $matched;
            }

            if (empty($matched)) {
                $words[$prevMatch . $i] .= $token . " ";
            }

        }

        $wildcardData = array();

        foreach ($words as $index => $word) {
            $word = trim(mb_strtolower($word));
            $wildcardData[] = array(
                "original" => $word,
                "replaced" => $this->string->substituteWords($word)
            );
        }

        return $wildcardData;
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
        $wildcardData = $this->extractWildcardData($node->getExtraData('matching_tokens'));

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
        $setters = $template->getElementsByTagName("set");
        $noOfSetters = $setters->length;
        for ($i = 0; $i < $noOfSetters; $i++) {
            $setter = $setters->item($i);
            $variableName = $setter->getAttribute("name");
            $variableData = $setter->nodeValue;
            if ($variableName == 'topic') { // Save topic in separate memory cache
                $this->memory->rememberTopic($variableData);
            } else {
                $this->memory->rememberUserData("variables.{$variableName}", $variableData);
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
        $thinks = $template->getElementsByTagName("think");
        $noOfThinks = $thinks->length;
        for ($i = 0; $i < $noOfThinks; $i++) {
            $think = $thinks->item(0);
            $think->parentNode->removeChild($think);
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
        if ($emotionTag = $template->getElementsByTagName("emotion")->item(0)) {
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
        $learnPath = $this->kernel->getContainer()->getParameter('path_aiml_learn');

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
        if ($learnTag = $template->getElementsByTagName("learn")->item(0)) {
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
        $botProps = $template->getElementsByTagName("bot");
        $noOfBotProps = $botProps->length;
        for ($i = 0; $i < $noOfBotProps; $i++) {
            $botProp = $botProps->item(0);
            $propName = $botProp->getAttribute("name");
            $propValue = $this->memory->recallUserData("bot.{$propName}");
            $replacement = $template->ownerDocument->createTextNode($propValue);
            $botProp->parentNode->replaceChild($replacement, $botProp);
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
            $mappedValueRep = @mb_eregi_replace("[\b(userref)\b\b(botref)\b]", "", $mappedValue);

            if ($mappedValueRep) {
                $mappedValue = $mappedValueRep;
            }

            $mappedNode = $template->ownerDocument->createTextNode($mappedValue);
            $map->parentNode->replaceChild($mappedNode, $map);
        }
        return $this;
    }

    /**
     * @param $keyword
     * @param string $language
     * @param bool $random
     * @return bool|string
     */
    public function searchWikipedia($keyword, $language = "vi", $random = false)
    {
        $queries = array(
            'action' => 'query',
            'prop' => 'extracts',
            'exintro' => '',
            'explaintext' => '',
            'format' => 'json',
            'redirects' => '1',
            'titles' => $keyword
        );

        if ($random) {
            $queries['generator'] = 'random';
        }

        $queries = http_build_query($queries);
        $url = "http://{$language}.wikipedia.org/w/api.php?{$queries}";
        $data = @file_get_contents($url);

        if (!$data) {
            return false;
        }

        $data = json_decode($data, true);
        $pageData = array_shift($data['query']['pages']);

        if (isset($pageData['extract'])) {
            return mb_substr($pageData['extract'], 0, 700) . "... ";
        }

        return "";
    }

    /**
     * Map original value to a value defined in a map file
     * @param $originalValue
     * @param $filename
     * @return mixed
     */
    private function mapData($originalValue, $filename)
    {
        $path = $this->kernel->getContainer()->getParameter('path_maps') . DIRECTORY_SEPARATOR . $filename . ".json";
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
        $noOfCategories = $categories->length;
        for ($i = 0; $i < $noOfCategories; $i++) {
            // Get category
            $category = $categories->item($i);

            // Get pattern
            $pattern = $category->getElementsByTagName('pattern')->item(0);

            if ($pattern) {
                // Remove wildcard characters
                $tmpPattern = @mb_eregi_replace("[\#\_\^\*]", "", $pattern->nodeValue);

                if (!$tmpPattern) {
                    $tmpPattern = $pattern->nodeValue;
                }

                if ($this->containsForbiddenWords($tmpPattern)) {
                    $error = "Không thể học được vì chứa từ cấm!";
                    $errorNode = $category->ownerDocument->createTextNode($error);
                    $learnNode = $category->parentNode;
                    $learnNode->parentNode->appendChild($errorNode);
                } else {
                    // Get template
                    $template = $category->getElementsByTagName('template')->item(0);

                    // Check if pattern an template are the same, if they are identical, bot won't learn anything
                    $same = ($this->string->stringcmp(trim($tmpPattern), trim($template->nodeValue)) == 0);

                    // Convert pattern value to uppercase for saving
                    $pattern->nodeValue = mb_strtoupper($pattern->nodeValue);

                    $importedNode = $learnAiml->importNode($category, true);

                    if ($oldNode = $this->hasPatternString($learnAiml, $tmpPattern)) {
                        if (!$same) {
                            $learnAiml->documentElement->replaceChild($importedNode, $oldNode);
                        }
                    } else {
                        if (!$same) {
                            $learnAiml->documentElement->appendChild($importedNode);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get words in a set
     * @param $set
     * @return array|mixed
     */
    public function getSetWords($set)
    {
        $setPath = $this->kernel->getContainer()->getParameter('path_sets') . DIRECTORY_SEPARATOR . $set . '.json';
        $setWordsJson = @file_get_contents($setPath);

        if ($setWordsJson) {
            return json_decode($setWordsJson, true);
        }

        return array();
    }

    /**
     * @param $pattern
     * @return bool
     */
    private function containsForbiddenWords($pattern)
    {
        $forbiddenWords = $this->getSetWords('forbidden');
        foreach ($forbiddenWords as $word) {
            return mb_ereg_match(".*\b({$word})\b", $pattern);
        }
        return false;
    }

    /**
     * Check if an AIML DOM Document already has a pattern
     * @param \DOMDocument $aiml
     * @param string $pattern
     * @return bool|\DOMNode
     */
    private function hasPatternString($aiml, $pattern)
    {
        $patterns = $aiml->getElementsByTagName('pattern');
        $nPatterns = $patterns->length;

        for ($i = 0; $i < $nPatterns; $i++) {
            $aimlPattern = $patterns->item($i);
            if ($this->string->stringcmp($aimlPattern->textContent, $pattern) == 0) {
                return $aimlPattern->parentNode;
            }
        }
        return false;
    }
}
