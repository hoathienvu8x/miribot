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
    public function produceQueries($input, $that, $topic)
    {
        $query = $this->standardize($input);
        $query[] = "<that>";
        $query = array_merge($query, $this->standardize($that));
        $query[] = "<topic>";
        $query = array_merge($query, $this->standardize($topic));
        return $query;
    }

    protected function standardize($string)
    {
        // 1. Normalize the input to produce a string of text in uppercase
        $string = $this->normalize($string);

        // 2. Produce a set of tokens
        return $this->tokenize($string);
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
        return preg_split("/[\.\!\?\;\:\n\t]/", trim($text), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Get string tokens
     * @param $text
     * @return array
     */
    public function tokenize($text)
    {
        $tokens = mb_split('[^\w\_\^\#\*\d]', $text);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }

    /**
     * Obtain allowed HTML tags
     * @return string
     */
    public function getAllowedHTMLTags()
    {
        $tagList = array(
            '<html>',
            '<a>',
            '<b>',
            '<img>',
            '<br>',
            '<div>',
            '<p>',
            '<span>',
            '<table>',
            '<th>',
            '<tr>',
            '<td>',
            '<thead>',
            '<tbody>',
            '<strong>',
            '<em>',
            '<i>',
            '<fieldset>',
            '<legend>',
            '<iframe>',
            '<embed>',
            '<script>'
        );

        return implode("", $tagList);
    }

    /**
     * Obtain allowed AIML tags
     * @return string
     */
    public function getAllowedAIMLTagList()
    {
        $tagList = array(
            '<bot>',
            '<category>',
            '<condition>',
            '<date>',
            '<denormalize>',
            '<eval>',
            '<explode>',
            '<first>',
            '<formal>',
            '<gender>',
            '<get>',
            '<id>',
            '<input>',
            '<interval>',
            '<learn>',
            '<li>',
            '<loop>',
            '<lowercase>',
            '<map>',
            '<normalize>',
            '<pattern>',
            '<person>',
            '<person2>',
            '<program>',
            '<random>',
            '<request>',
            '<response>',
            '<rest>',
            '<sentence>',
            '<set>',
            '<size>',
            '<sr>',
            '<srai>',
            '<sraix>',
            '<star>',
            '<template>',
            '<that>',
            '<thatstar>',
            '<think>',
            '<topic>',
            '<topicstar>',
            '<uppercase>',
            '<emotion>',
            // HTML tags
            '<html>',
            '<a>',
            '<b>',
            '<img>',
            '<br>',
            '<div>',
            '<p>',
            '<span>',
            '<table>',
            '<th>',
            '<tr>',
            '<td>',
            '<thead>',
            '<tbody>',
            '<strong>',
            '<em>',
            '<i>',
            '<fieldset>',
            '<legend>',
            '<iframe>',
            '<embed>',
            '<script>'
        );

        return implode("", $tagList);
    }
}