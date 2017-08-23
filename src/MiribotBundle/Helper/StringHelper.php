<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 17:22
 */

namespace MiribotBundle\Helper;


class StringHelper
{
    /**
     * Standardize user input and produce queries for the bot
     * @param $input
     * @return array
     */
    public function produceQueries($input)
    {
        // 1. Normalize the input to produce a string of text in uppercase
        $input = $this->normalize($input);

        // 2. Produce a set of tokens
        return $this->tokenize($input);
    }

    /**
     * Normalize the text
     * @param $text
     * @return string
     */
    public function normalize($text)
    {
        return mb_strtoupper($text);
    }

    /**
     * Split the input text into sentences
     * @param $text
     * @return array
     */
    public function sentenceSplitting($text)
    {
        return preg_split("/[.!?\n]/", trim($text), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get string tokens
     * @param $text
     * @return array
     */
    public function tokenize($text)
    {
        $tokens = mb_split('\W', $text);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }
}