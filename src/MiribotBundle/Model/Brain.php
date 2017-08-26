<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 20-Aug-17
 * Time: 11:13
 */

namespace MiribotBundle\Model;

use MiribotBundle\Helper\Helper;
use MiribotBundle\Model\Graphmaster\Nodemapper;
use Twig\Node\Node;

class Brain
{
    /**
     * @var Graphmaster
     */
    protected $knowledge;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Brain constructor.
     * @param Graphmaster $graph
     * @param Helper $helper
     */
    public function __construct(Graphmaster $graph, Helper $helper)
    {
        $this->knowledge = $graph;
        $this->helper = $helper;
    }

    /**
     * Get answer from bot's brain
     * @param $userInput
     * @return string
     */
    public function getAnswer($userInput)
    {
        // Pre-process user input to break it into sentences
        $sentences = $this->helper->string->sentenceSplitting($userInput);

        // Think for answer
        $answer = $this->thinkForAnswer($sentences);

        // Save bot's last sentence
        $this->helper->memory->rememberLastSentence($answer);

        return empty($answer) ? "..." : $answer;
    }

    /**
     * Think for an answer
     * @param $sentences
     * @return string
     */
    protected function thinkForAnswer($sentences) {

        $answer = "";

        // The sentences serve as query string for the brain to get its answer
        foreach ($sentences as $sentence) {

            // Get last sentence of the bot as <that>
            $that = $this->helper->memory->recallLastSentence();

            // Get <topic> of the bot
            $topic = $this->helper->memory->recallTopic();

            // Produce a query for the bot
            $query = $this->helper->string->produceQueries($sentence, $that, $topic);

            // Think for an answer and get a match answer template
            $matchedAnswerTemplate = $this->queryKnowledge($query, $sentence, $that, $topic);

            // Combine all answer templates to get final answers
            if (!empty($matchedAnswerTemplate)) {
                if (!empty($answer)) {
                    $lastChar = substr($answer, -1);
                    if (ctype_alnum($lastChar)) {
                        $answer .= "."; // Add a period to the answer before moving on
                    }
                }
                $answer .= " " . $matchedAnswerTemplate;
            }
        }

        return trim($answer);
    }

    /**
     * Query the knowledge
     * @param array $query A set of query tokens
     * @param string $queryString Original query string
     * @return string
     */
    protected function queryKnowledge($query, $queryString, $that, $topic)
    {
        // Find a word node that has template matches the query pattern
        $node = $this->knowledge->matchQueryPattern($query);

        // Return blank answer if we cannot find the node
        if (!$node) {
            return "";
        }

        $tokenizedInput = $this->helper->string->tokenize($queryString);
        $node = $this->produceResponse($node, $tokenizedInput, $that, $topic);
        $answer = $node->getTemplate()->textContent;

        return ucfirst(trim($answer));
    }

    protected function produceResponse(Nodemapper $node, $tokenizedInput, $that, $topic)
    {
        $referenceNodes = array();

        // Collect srai reference nodes
        if ($node->getTemplate()->getElementsByTagName("srai")->length > 0) {
            $srais = $node->getTemplate()->getElementsByTagName("srai");
            $noOfSrais = $srais->length;
            for($i = 0; $i < $noOfSrais; $i++) {
                $srai = $srais->item(0);
                $this->helper->template->replaceWildcards($srai, $node, $tokenizedInput);
                $referenceNode = $this->knowledge->getReferenceNode($srai, $that, $topic);
                $referenceNodes[] = $this->produceResponse($referenceNode, $tokenizedInput, $that, $topic);
            }
        }

        // Process the node template to get final response
        $this->helper->template->processNodeTemplate($node, $referenceNodes, $tokenizedInput);

        return $node;
    }
}
