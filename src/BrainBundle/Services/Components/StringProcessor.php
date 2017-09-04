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
    /**
     * Get string tokens
     * @param string $text
     * @param string $delimiterRegex
     * @return array
     */
    public function tokenize($text, $delimiterRegex = '[^\w\_\^\#\*\+\-\*\/\(\)\d]')
    {
        $tokens = mb_split($delimiterRegex, $text);
        $tokens = array_map('trim', $tokens);
        return array_filter($tokens);
    }
}