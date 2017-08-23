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
        // Get last sentence of the bot
        $lastBotSentence = $this->helper->memory->recallLastSentence();

        $queryString = $userInput;

        // Append bot's last sentence to user input
        $queryString .= " " . $lastBotSentence;

        // Pre-process user input to break it into sentences
        $sentences = $this->helper->string->produceQueries($queryString);

        // Initialize bot's answer
        $answer = "";

        // The sentences serve as queries for the brain to get its answer
        foreach ($sentences as $query) {
            $a = $this->thinkForAnswer($query, $userInput);
            if (!empty($a)) {
                $answer .= $a . " ";
            }
        }

        // Save bot's last sentence
        $this->helper->memory->rememberLastSentence($answer);

        return empty($answer) ? "..." : $answer;
    }

    /**
     * Think for an answer
     * @param $query
     * @return string
     */
    protected function thinkForAnswer($query, $userInput)
    {
        // 1. Find a word node that has template matches the query pattern
        $wordNode = $this->knowledge->matchQueryPattern($query);

        if (!$wordNode) {
            return "";
        }

        // 2. Process the node template
        $answer = $this->helper->template->getResponseFromTemplate($wordNode, $this->helper->string->tokenize($userInput));

        return trim($answer);
    }
}