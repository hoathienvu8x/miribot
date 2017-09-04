<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 04-Sep-17
 * Time: 12:03
 */

namespace BrainBundle\Services\Components;

use ChrisKonnertz\StringCalc\StringCalc;

class MathProcessor
{
    protected $string;

    public function __construct(StringProcessor $string) {
        $this->string = $string;
    }

    /**
     * Calculate mathematical expression inside a string
     * @param $string
     * @return string
     */
    public function calculateMathInString($string)
    {
        try {
            $stringCalc = new StringCalc();
            $string = $this->produceMathExpression($string);
            return (string) $stringCalc->calculate($string);
        } catch (\Exception $exception) {
            return "";
        }
    }

    /**
     * Produce a math expression from text
     * @param $text
     * @return string
     */
    public function produceMathExpression($text)
    {
        $tokens = $this->string->tokenize($text, '[^\w\_\^\#\*\+\-\*\/\(\)\.\d]');

        $mathString = "";

        foreach ($tokens as $token) {
            foreach($this->allowedMathOperations() as $operator) {
                if (mb_ereg_match($operator, $token)) {
                    $mathString .= " " . $token;
                }
            }
        }

        return trim($mathString);
    }

    /**
     * All allowed mathematics operations
     * @return array
     */
    public function allowedMathOperations()
    {
        return array(
            '\+', '\-', '\*', '\/', '\d', '\(', '\)',
            '\b(abs)\b', '\b(sin)\b', '\b(cos)\b', '\b(tan)\b',
            '\b(min)\b', '\b(max)\b', '\b(pi)\b', '\b(ceil)\b',
            '\b(floor)\b', '\b(round)\b', '\b(sqrt)\b', '\b(pow)\b',
            '\b(log)\b', '\b(log10)\b'
        );
    }
}