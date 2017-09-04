<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 04-Sep-17
 * Time: 12:25
 */

namespace tests\BrainBundle\Services\Components;

use BrainBundle\Services\Components\MathProcessor;
use BrainBundle\Services\Components\StringProcessor;
use PHPUnit\Framework\TestCase;

class MathProcessorTest extends TestCase
{
    protected $txt = "This is a text compose of 1 + 2 and -abs(-5) + (7 + 8 * 9) - 2";
    protected $math;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->math = new MathProcessor(new StringProcessor());
    }

    public function testCalculateMathInString()
    {
        $result = $this->math->calculateMathInString($this->txt);
        $this->assertEquals("75", $result);
    }

    public function testProduceMathExpression()
    {
        $result = $this->math->produceMathExpression($this->txt);
        $this->assertEquals("1 + 2 -abs(-5) + (7 + 8 * 9) - 2", $result);
    }
}