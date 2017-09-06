<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 04-Sep-17
 * Time: 12:04
 */

namespace BrainBundle\Services\Components;

class StringProcessor
{
    protected $array;

    public function __construct(ArrayProcessor $array)
    {
        $this->array = $array;
    }

    /**
     * Normalize a text
     * @param $text
     * @return mixed|string
     */
    public function normalize($text)
    {
        $trimmed = @mb_eregi_replace("\s+", " ", trim($text));
        return mb_strtolower($trimmed);
    }

    /**
     * Split the input text into sentences
     * @param $text
     * @param $regex
     * @return array
     */
    public function sentenceSplitting($text, $regex = "[\.\!\?\;\:\t]")
    {
        $trimmed = @mb_eregi_replace("\s+", " ", trim($text));
        $sentences = array_filter(@mb_split($regex, $trimmed));
        return array_values($sentences);
    }

    /**
     * Get string tokens
     * @param string $text
     * @param string $delimiterRegex
     * @return array
     */
    public function tokenize($text, $delimiterRegex = '[^\w\_\^\#\*\+\-\*\/\(\)\d]')
    {
        $tokens = @mb_split($delimiterRegex, $text);
        $mapped = array_map('trim', $tokens);
        $filtered = array_filter($mapped);
        return array_values($filtered);
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
            $encoding = @mb_internal_encoding();
        }
        return strcmp(@mb_strtoupper($str1, $encoding), @mb_strtoupper($str2, $encoding));
    }

    /**
     * Extract word n-grams
     * @param $text
     * @param $order
     * @return array
     */
    public function nGrams($text, $order = 3)
    {
        $tokens = $this->tokenize($text, '\s+');

        $nGrams = array();

        for ($i = 0; $i <= count($tokens) + $order; $i++) {
            $gram = array_slice($tokens, $i, $order);

            if (empty($gram)) {
                break;
            }

            $noOfWords = count($gram);

            // If we reach the end word of the text, fill the last gram with blanks
            if ($noOfWords < $order && $noOfWords >= 1) {
                $blanks = array_fill(0, $order - $noOfWords, "");
                $gram = array_merge($gram, $blanks);
            }

            $nGrams[] = $gram;
        }

        return $nGrams;
    }

    /**
     * @param $text
     * @param int $order
     * @return array
     */
    public function markovWordChain($text, $order = 1)
    {
        $tokens = $this->tokenize($text, '\s+');
        $noOfTokens = count($tokens);

        $markovChain = array();

        for ($i = 0; $i <= $noOfTokens + $order; $i++) {
            $gram = array_slice($tokens, $i, $order);

            if (empty($gram)) {
                break;
            }

            $noOfWords = count($gram);

            // If we reach the end word of the text, fill the last gram with blanks
            if ($noOfWords < $order && $noOfWords >= 1) {
                $blanks = array_fill(0, $order - $noOfWords, "");
                $gram = array_merge($gram, $blanks);
            }

            $gram = implode(" ", $gram);

            if (!isset($markovChain[$gram])) {
                $markovChain[$gram] = array();
            }

            if (isset($tokens[$i + $order])) {
                $next = $tokens[$i + $order];
                if (!isset($markovChain[$gram][$next])) {
                    $markovChain[$gram][$next] = 0;
                }
                $markovChain[$gram][$next]++;
            }
        }

        return $markovChain;
    }

    /**
     * Generate a text from another text
     * @param $text
     * @param $length
     * @param int $order
     * @return string
     */
    public function markovGenerator($text, $length, $order = 3)
    {
        $chain = $this->markovWordChain($text, $order);

        // Get a start word
        $currentGram = array_rand($chain, 1);
        $currentToken = $chain[$currentGram];

        $result = $currentGram;
        $i = $order;

        while ($i < $length) {
            $word = $this->array->getRandomWeightedElement($currentToken);

            if (empty($word)) {
                break;
            }

            $result .= " " . $word;
            $resultTokens = $this->tokenize($result, "\s+");
            $currentGram = array_slice($resultTokens, count($resultTokens) - $order, $order);
            $currentGram = implode(" ", $currentGram);

            if (isset($chain[$currentGram])) {
                $currentToken = $chain[$currentGram];
                $i++;
            }
        }

        return ucfirst($result);
    }
}