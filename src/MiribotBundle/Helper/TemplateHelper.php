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
        $wildcardData = array_diff($userInputTokens, $pattern);

        $template = $wordNode->getTemplate();

        // Select random response if necessary
        if ($template->random) {
            $template = $this->getRandomResponse($template);
        }

        // Replace all template wildcards with user input
        $this->replaceWildcards($template, $wildcardData);

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
        if ($template->star) {
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
    }
}