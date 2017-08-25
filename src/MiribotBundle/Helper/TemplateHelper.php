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
        $pattern = explode(" ", $wordNode->getPattern());
        $userInputTokens = array_map('strtoupper', $userInputTokens);
        $wildcardData = array_diff($userInputTokens, $pattern);

        $template = $wordNode->getTemplate();

        // Select random response if necessary
        if ($template->random) {
            $template = $this->getRandomResponse($template);
        }

        // Replace all template wildcards with user input
        $this->replaceWildcards($template, $wildcardData);

        // Handle getting and setting variables from the template
        $this->handleGetters($template);
        $this->handleSetters($template);

        return $template->__toString();
    }

    /**
     * Get random template response
     * @param \SimpleXMLElement $template
     * @return \SimpleXMLElement
     */
    protected function getRandomResponse(&$template)
    {
        // Get maximum response index
        $maxIdx = $template->random->li->count() - 1;

        // Randomize response content from min to max index
        /** @var \SimpleXMLElement $response */
        $response = $template->random->li[mt_rand(0, $maxIdx)];

        return $response;
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
        array_walk($stars, function(&$v) {
            $v = addcslashes($v, '\"\/');
            $v = "/{$v}/";
        });

        $rawXml = preg_replace($stars, $wildcardData, $rawXml);
        $template = new \SimpleXMLElement($rawXml);
    }

    /**
    * Handle getter tags
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
            array_walk($getters, function(&$v) {
                $v = addcslashes($v, '\"\/');
                $v = "/{$v}/";
            });

            $rawXml = preg_replace($getters, $variableValues, $rawXml);
            $template = new \SimpleXMLElement($rawXml);
        }
    }

    /**
    * Handle setter tags
    */
    protected function handleSetters(&$template)
    {
        if ($setters = $template->set) {
            foreach ($setters as $setter) {
                // Set require variable values to the memory
                $attributes = $setter->attributes();
                foreach ($attributes as $key => $value) {
                    if ($key === "name") {
                        $value = $value->__toString();
                        $this->memory->rememberUserData("variables.{$value}", $setter->__toString());
                    }
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
            foreach($attributes as $key => $value) {
                if ($key === "name") {
                    $variableNames[] = $value->__toString();
                }
            }
        }
        return $variableNames;
    }

    /**
    * Get variable values from memory based on the collected names
    */
    private function getVariableValuesFromMemory(&$variableNames)
    {
        $variableValues = array();
        foreach($variableNames as $key) {
            $variableValues[$key] = $this->memory->recallUserData("variables.{$key}");
        }
        return $variableValues;
    }
}
