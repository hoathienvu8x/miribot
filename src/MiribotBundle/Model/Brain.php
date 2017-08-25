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
        // Get last sentence of the bot as <that>
        $lastBotSentence = $this->helper->memory->recallLastSentence();

        // Append last bot sentence to user input
        $queryString = $userInput . " " . $lastBotSentence;

        // Pre-process user input to break it into sentences
        $sentences = $this->helper->string->sentenceSplitting($queryString);

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
            // Produce a query for the bot
            $query = $this->helper->string->produceQueries($sentence);

            // Think for an answer and get a match answer template
            $matchedAnswerTemplate = $this->queryKnowledge($query, $sentence);

            // Combine all answer templates to get final answers
            if (!empty($matchedAnswerTemplate)) {
                $answer .= $matchedAnswerTemplate . " ";
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
    protected function queryKnowledge($query, $queryString)
    {
        // 1. Find a word node that has template matches the query pattern
        $wordNode = $this->knowledge->matchQueryPattern($query);

        // Return blank answer if we cannot find the node
        if (!$wordNode) {
            return "";
        }

        // 2. Process the node template to get final response
        $tokenizedInput = $this->helper->string->tokenize($queryString);
        $answer = $this->helper->template->getResponseFromTemplate($wordNode, $tokenizedInput);

        return ucfirst(trim($answer));
    }
}
