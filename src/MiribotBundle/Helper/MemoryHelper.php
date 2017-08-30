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
use Symfony\Component\HttpFoundation\Session\Session;

class MemoryHelper extends Session
{
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
        return $this->get('bot_last_sentence');
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
        return $this->get('bot_topic');
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
     */
    public function forgetUserData($name)
    {
        $this->remove("user_data.{$name}");
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
