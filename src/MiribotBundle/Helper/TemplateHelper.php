<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:20
 */

namespace MiribotBundle\Helper;


use MiribotBundle\Model\Graphmaster\Nodemapper;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig\Node\Node;

class TemplateHelper
{
    protected $memory;

    public function __construct(MemoryHelper $memory)
    {
        $this->memory = $memory;
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
        $this->processRandomResponse($template)// Select random response if necessary
        ->processConditions($template)// Process conditional tags
        ->processReferences($template, $referenceNodes)// Get all references
        ->replaceWildcards($template, $node, $userInputTokens)// Replace all template wildcards with user input
        ->handleGetters($template)// Handle get tags
        ->handleSetters($template)// Handle set tags
        ->handleThinks($template);// Handle think tags

        // Set the processed template back to the node
        $node->setTemplate($template);
    }

    /**
     * @param \DOMElement $template
     * @param array $referenceNodes
     * @return $this
     */
    public function processReferences(&$template, $referenceNodes)
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
    public function processConditions(&$template)
    {
        /** @var \DOMElement $condition */
        if ($condition = $template->getElementsByTagName("condition")->item(0)) {
            $variableName = $condition->getAttribute("name");
            $variableData = $this->memory->recallUserData("variables.{$variableName}");

            $default = $template;
            $matched = false;

            if ($lis = $condition->getElementsByTagName("li")) {
                /** @var \DOMElement $li */
                foreach ($lis as $li) {
                    if (!$li->hasAttribute("value")) {
                        $default = $li;
                    } else {
                        if ($li->getAttribute("value") == $variableData) {
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
    public function processRandomResponse(&$template)
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
        // Filter out wildcard data
        foreach ($userInputTokens as $id => $token) {
            foreach ($pattern as $patternWord) {
                if (strcasecmp($token, $patternWord) == 0) {
                    unset($userInputTokens[$id]);
                }
            }
        }

        return $userInputTokens;
    }

    /**
     * Replace all wildcards in template with user input values
     * @param \DOMElement $template
     * @param Nodemapper $node
     * @param array $userInputTokens
     * @return $this
     */
    public function replaceWildcards(&$template, $node, $userInputTokens)
    {
        // Extract wildcard data
        $wildcardData = $this->extractWildcardData(explode(" ", $node->getPattern()), $userInputTokens);

        $stars = $template->getElementsByTagName("star");
        $noOfStars = $stars->length;
        for ($i = 0; $i < $noOfStars; $i++) {
            $wildcardValue = $template->ownerDocument->createTextNode(array_shift($wildcardData));
            $star = $stars->item(0);
            $star->parentNode->replaceChild($wildcardValue, $star);
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
}
