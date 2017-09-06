<?php
/**
 * This is the Wernicke's area of Miri's brain
 * This area helps Miri understands speech (metaphor
 * for input text content)
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 04-Sep-17
 * Time: 11:13
 */

namespace BrainBundle\Services;

use BrainBundle\Services\Components\MathProcessor;
use BrainBundle\Services\Components\StringProcessor;

class Wernicke
{
    protected $math;
    protected $string;

    public function __construct(MathProcessor $math, StringProcessor $string)
    {
        $this->math = $math;
        $this->string = $string;
    }

    public function processTextInput($input)
    {
    }
}