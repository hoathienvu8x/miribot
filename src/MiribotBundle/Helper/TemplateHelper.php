<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:20
 */

namespace MiribotBundle\Helper;


use MiribotBundle\Model\Graphmaster\Nodemapper;

class TemplateHelper
{
    protected $memory;

    public function __construct(MemoryHelper $memory)
    {
        $this->memory = $memory;
    }

    /**
     * Process template logics
     * @param Nodemapper $wordNode
     * @param array $userInputTokens
     * @return string
     */
    public function getResponseFromTemplate($wordNode, $userInputTokens)
    {
        // Retrieve node's template
        $template = $wordNode->getTemplate();

        // Extract wildcard data
        $wildcardData = $this->extractWildcardData(explode(" ", $wordNode->getPattern()), $userInputTokens);

        // Process template data
        $this->processRandomResponse($template)// Select random response if necessary
        ->processConditions($template)// Process conditional tags
        ->replaceWildcards($template, $wildcardData)// Replace all template wildcards with user input
        ->handleGetters($template)// Handle get tags
        ->handleSetters($template); // Handle set tags

        return $template->__toString();
    }

    /**
     * Process condition tags
     * @param $template
     * @return $this
     */
    protected function processConditions(&$template)
    {
        /** @var \SimpleXMLElement $condition */
        if ($condition = $template->condition) {
            $variableName = "";
            $variableData = "";
            /**
             * @var string $key
             * @var \SimpleXMLElement $value
             */
            foreach ($condition->attributes() as $key => $value) {
                if ($key == "name") {
                    $variableName = $value->__toString();
                    $variableData = $this->memory->recallUserData("variables.{$variableName}");
                }
            }

            $default = $template;
            $matched = false;
            if ($possibleResponseTemplates = $condition->li) {
                /** @var \SimpleXMLElement $item */
                foreach ($possibleResponseTemplates as $item) {
                    // Save list item without attribute as default response template
                    if ($item->attributes()->count() == 0) {
                        $default = $item;
                    }

                    /**
                     * @var string $key
                     * @var \SimpleXMLElement $value
                     */
                    foreach ($item->attributes() as $key => $value) {
                        if ($key == "value" && $variableData == $value->__toString()) {
                            $template = $item;
                            $matched = true;
                            break;
                        }
                    }
                }

                // If no response template matched then fallback to default
                if (!$matched) {
                    $template = $default;
                }
            }
        }

        return $this;
    }

    /**
     * Get random template response
     * @param \SimpleXMLElement $template
     * @return $this
     */
    protected function processRandomResponse(&$template)
    {
        if ($template->random) {
            // Get maximum response index
            $maxIdx = $template->random->li->count() - 1;

            // Randomize response content from min to max index
            /** @var \SimpleXMLElement $response */
            $template = $template->random->li[mt_rand(0, $maxIdx)];
        }
        return $this;
    }

    /**
     * @param array $pattern
     * @param array $userInputTokens
     * @return array
     */
    protected function extractWildcardData($pattern, $userInputTokens)
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
     * @param \SimpleXMLElement $template
     * @param $wildcardData
     */
    protected function replaceWildcards(&$template, $wildcardData)
    {
        // Replace all wildcards with user values
        $rawXml = $template->asXML();
        $stars = array();
        preg_match_all('/<star[^>]*\/>/', $rawXml, $stars);
        $stars = array_shift($stars);
        array_walk($stars, function (&$v) {
            $v = addcslashes($v, '\"\/');
            $v = "/{$v}/";
        });

        $rawXml = preg_replace($stars, $wildcardData, $rawXml);
        $template = new \SimpleXMLElement($rawXml);
        return $this;
    }

    /**
     * Handle getter tags
     * @param \SimpleXMLElement $template
     */
    protected function handleGetters(&$template)
    {
        if ($getters = $template->get) {
            // Get required variable values
            $variableNames = $this->collectNamesOfVariables($getters);
            $variableValues = $this->getVariableValuesFromMemory($variableNames);

            // Replace variable values for each getter tag found
            $rawXml = $template->asXML();
            $getters = array();
            preg_match_all('/<get[^>]*\/>/', $rawXml, $getters);
            $getters = array_shift($getters);
            array_walk($getters, function (&$v) {
                $v = addcslashes($v, '\"\/');
                $v = "/{$v}/";
            });

            $rawXml = preg_replace($getters, $variableValues, $rawXml);
            $template = new \SimpleXMLElement($rawXml);
        }

        return $this;
    }

    /**
     * Handle setter tags
     * @param \SimpleXMLElement $template
     */
    protected function handleSetters(&$template)
    {
        // Get normal setters
        if ($setters = $template->set) {
            $this->setVariables($setters);
        }

        // Get setters inside think
        /** @var \SimpleXMLElement $think */
        if ($think = $template->think) {
            if ($setters = $think->set) {
                $this->setVariables($setters);
            }
        }

        return $this;
    }

    /**
     * @param $setters
     */
    private function setVariables(&$setters)
    {
        /** @var \SimpleXMLElement $setter */
        foreach ($setters as $setter) {
            // Set require variable values to the memory
            $attributes = $setter->attributes();
            /**
             * @var string $key
             * @var \SimpleXMLElement $value
             */
            foreach ($attributes as $key => $value) {
                if ($key === "name") {
                    $value = $value->__toString();
                    $this->memory->rememberUserData("variables.{$value}", $setter->__toString());
                }
            }
        }
    }

    /**
     * Collect names of variables from getters
     */
    private function collectNamesOfVariables(&$getters)
    {
        $variableNames = array();
        foreach ($getters as $getter) {
            $attributes = $getter->attributes();
            foreach ($attributes as $key => $value) {
                if ($key === "name") {
                    $variableNames[] = $value->__toString();
                }
            }
        }
        return $variableNames;
    }

    /**
     * @param array $variableNames
     * @return array
     */
    private function getVariableValuesFromMemory(&$variableNames)
    {
        $variableValues = array();
        foreach ($variableNames as $key) {
            $variableValues[$key] = $this->memory->recallUserData("variables.{$key}");
        }
        return $variableValues;
    }
}
