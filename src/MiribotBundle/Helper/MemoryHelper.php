<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 22:15
 */

namespace MiribotBundle\Helper;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

class MemoryHelper extends FilesystemAdapter
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
        $lastSentence = $this->getItem('bot_last_sentence');
        $lastSentence->set(end($sentences));
        $this->save($lastSentence);
    }

    /**
     * Recall the last sentence of bot's response
     * @return mixed|null|string
     */
    public function recallLastSentence()
    {
        $lastSentence = $this->getItem('bot_last_sentence');
        return $lastSentence->get();
    }

    /**
     * Remember bot's topic
     * @param $topic
     */
    public function rememberTopic($topic)
    {
        $botTopic = $this->getItem('bot_topic');
        $botTopic->set($topic);
        $this->save($botTopic);
    }

    /**
     * Recall the last topic of bot's response
     * @return mixed|null|string
     */
    public function recallTopic()
    {
        $botTopic = $this->getItem('bot_topic');
        return $botTopic->get();
    }

    /**
     * Remember user data
     * @param $name
     * @param $data
     */
    public function rememberUserData($name, $data)
    {
        $userData = $this->getItem("user_data.{$name}");
        $userData->set($data);
        $this->save($userData);
    }

    /**
     * Recall user data
     * @param string $name
     * @return mixed|null
     */
    public function recallUserData($name)
    {
        $userData = $this->getItem("user_data.{$name}");
        return $userData->get();
    }

    /**
     * Forget a user data
     * @param $name
     */
    public function forgetUserData($name)
    {
        $this->deleteItem("user_data.{$name}");
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
