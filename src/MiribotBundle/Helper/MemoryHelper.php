<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 22:15
 */

namespace MiribotBundle\Helper;

use Symfony\Component\Cache\Simple\FilesystemCache;

class MemoryHelper extends FilesystemCache
{
    /**
     * MemoryHelper constructor.
     * @param string $namespace
     * @param int $defaultLifetime
     * @param string $directory
     */
    public function __construct($namespace = '', $defaultLifetime = 3600, $directory = null)
    {
        parent::__construct($namespace, $defaultLifetime, $directory);
    }

    /**
     * Remember the last sentence of bot's response
     * @param $answer
     */
    public function rememberLastSentence($answer)
    {
        $sentences = $this->sentenceSplitting($answer);
        $this->set('bot_last_sentence', end($sentences));
    }

    /**
     * Recall the last sentence of bot's response
     * @return mixed|null|string
     */
    public function recallLastSentence()
    {
        $sentence = $this->get('bot_last_sentence');
        return $sentence ? $sentence : "";
    }

    /**
     * Remember bot's topic
     * @param $topic
     */
    public function rememberTopic($topic)
    {
        $this->set('bot_topic', $topic);
    }

    /**
     * Recall the last topic of bot's response
     * @return mixed|null|string
     */
    public function recallTopic()
    {
        $topic = $this->get('bot_topic');
        return $topic ? $topic : "";
    }

    /**
     * Remember user data
     * @param $name
     * @param $data
     */
    public function rememberUserData($name, $data)
    {
        $this->set("user_data.{$name}", $data);
    }

    /**
     * Recall user data
     * @param string $name
     * @return mixed|null
     */
    public function recallUserData($name)
    {
        return $this->get("user_data.{$name}");
    }

    /**
     * Forget a user data
     * @param $name
     * @return bool
     */
    public function forgetUserData($name)
    {
        return $this->delete("user_data.{$name}");
    }

    /**
     * Remember graphmaster
     * @param $knowledge
     */
    public function rememberGraphmasterData($knowledge)
    {
        $this->set("bot_graphmaster", $knowledge, 7200);
    }

    /**
     * Recall graphmaster
     * @return mixed|null
     */
    public function recallGraphmasterData()
    {
        return $this->get("bot_graphmaster", false);
    }

    /**
     * Forget graphmaster
     */
    public function forgetGraphmasterData()
    {
        $this->delete("bot_graphmaster");
    }

    /**
     * Split the input text into sentences
     * @param $text
     * @return array
     */
    protected function sentenceSplitting($text)
    {
        return preg_split("/[\.\!\?\;\:\n\t]/", trim($text), -1, PREG_SPLIT_NO_EMPTY);
    }
}
