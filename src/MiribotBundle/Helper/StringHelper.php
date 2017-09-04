<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 21-Aug-17
 * Time: 17:22
 */

namespace MiribotBundle\Helper;


use Symfony\Component\HttpKernel\Kernel;

class StringHelper
{
    protected $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Substitute words in the user input
     * @param $string
     * @return mixed
     */
    public function substituteWords($string)
    {
        $subsPath = $this->kernel->getContainer()->getParameter('path_substitutes');
        $subsFiles = glob($subsPath . DIRECTORY_SEPARATOR . "*.json");

        foreach ($subsFiles as $subsFile) {
            // Read and decode file
            $json = @file_get_contents($subsFile);
            if ($json && $substitutes = json_decode($json, true)) {
                foreach ($substitutes as $o => $r) {
                    $o = "\b({$o})\b";
                    $rep = @mb_eregi_replace($o, $r, $string, 'i');
                    if ($rep) {
                        $string = $rep;
                    }
                }
            }
        }

        return $string;
    }

    /**
     * Standardize user input and produce queries for the bot
     * @param $input
     * @param $that
     * @param $topic
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

    /**
     * Standardize a text
     * @param $string
     * @return array
     */
    protected function standardize($string)
    {
        // 0. Substitute words
        $string = $this->substituteWords($string);

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
     * @param string $regex
     * @return array
     */
    public function tokenize($text, $regex = '[^\w\_\^\#\*\+\-\*\/\(\)\d]')
    {
        $tokens = mb_split($regex, $text);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }

    /**
     * Produce a math expression from text
     * @param $text
     * @return string
     */
    public function produceMathExpression($text)
    {
        $tokens = $this->tokenize($text, '[^\w\_\^\#\*\+\-\*\/\(\)\.\d]');

        $mathString = "";

        foreach ($tokens as $token) {
            foreach($this->allowedMathOperators() as $operator) {
                if (mb_ereg_match($operator, $token)) {
                    $mathString .= " " . $token;
                }
            }
        }

        return trim($mathString);
    }

    public function allowedMathOperators()
    {
        return array(
            '\+', '\-', '\*', '\/', '\d', '\(', '\)',
            '\b(abs)\b', '\b(sin)\b', '\b(cos)\b', '\b(tan)\b',
            '\b(min)\b', '\b(max)\b', '\b(pi)\b', '\b(ceil)\b',
            '\b(floor)\b', '\b(round)\b', '\b(sqrt)\b', '\b(pow)\b',
            '\b(log)\b', '\b(log10)\b'
        );
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
     * @param $str1
     * @param $str2
     * @param null $encoding
     * @return int
     */
    public function stringcmp($str1, $str2, $encoding = null)
    {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }
        return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
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
            '<emotion>', // Bot's emotion
            '<wiki>', // Wikipedia search
            '<user>', // User info in session
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