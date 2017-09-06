<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 05-Sep-17
 * Time: 09:35
 */

namespace tests\BrainBundle\Services\Components;

use BrainBundle\Services\Components\ArrayProcessor;
use BrainBundle\Services\Components\StringProcessor;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class StringProcessorTest extends TestCase
{
    protected $string;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->string = new StringProcessor(new ArrayProcessor());
    }

    /**
     * Test string comparison function
     */
    public function testStringcmp()
    {
        $txt1 = "ThIS is A saMPLe TeXt";
        $txt2 = "this is a sample text";
        $txt3 = "this is not a sample text";

        $result1 = $this->string->stringcmp($txt1, $txt2);
        $result2 = $this->string->stringcmp($txt2, $txt3);
        $result3 = $this->string->stringcmp($txt1, $txt3);

        $this->assertEquals(0, $result1);
        $this->assertNotEquals(0, $result2);
        $this->assertNotEquals(0, $result3);
    }

    /**
     * Test tokenization function
     */
    public function testTokenize()
    {
        $txt = "this is a sample text. this # is _ another ^ text *, but well 1234? Hah! (Gotcha)";
        $expected = array(
            "this", "is", "a", "sample", "text", "this", "#",
            "is", "_", "another", "^", "text", "*", "but", "well",
            "1234", "Hah", "(Gotcha)"
        );

        $result = $this->string->tokenize($txt);
        $this->assertArraySubset($expected, $result);
        $this->assertEquals(count($expected), count($result));
    }

    /**
     * Test sentence splitting function
     */
    public function testSentenceSplitting()
    {
        $txt = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an 
        unknown printer took a galley of type and scrambled it to make a type specimen book. 
        It has survived not only five centuries, but also the leap into electronic typesetting, 
        remaining essentially unchanged. It was popularised in the 1960s with the release of 
        Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing 
        software like Aldus PageMaker including versions of Lorem Ipsum.";

        $result = $this->string->sentenceSplitting($txt);
        $this->assertEquals(4, count($result));
    }

    /**
     * Test string normalizer
     */
    public function testNormalize()
    {
        $txt = "THIS is A sAmPle TeXt.
        Can IT be norMALize? LET US SEE!";
        $expected = "this is a sample text. can it be normalize? let us see!";
        $result = $this->string->normalize($txt);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test word n-gram constructor
     */
    public function testNGrams()
    {
        $txt = "Lorem Ipsum is simply dummy text.";
        $expected = array(
            array("Lorem", "Ipsum", "is"),
            array("Ipsum", "is", "simply"),
            array("is", "simply", "dummy"),
            array("simply", "dummy", "text."),
            array("dummy", "text.", ""),
            array("text.", "", ""),
        );

        $result = $this->string->nGrams($txt, 3);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test construction of Markov word chain
     */
    public function testMarkovWordChain()
    {
        $txt = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an 
        unknown printer took a galley of type and scrambled it to make a type specimen book. 
        It has survived not only five centuries, but also the leap into electronic typesetting, 
        remaining essentially unchanged. It was popularised in the 1960s with the release of 
        Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing 
        software like Aldus PageMaker including versions of Lorem Ipsum.";

        $result = $this->string->markovWordChain($txt);
        $this->assertEquals(69, count($result));
    }

    /*public function testMarkovGenerator()
    {
        $txt = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. 
        Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an 
        unknown printer took a galley of type and scrambled it to make a type specimen book. 
        It has survived not only five \"centuries\", but also the leap into electronic typesetting, 
        remaining essentially unchanged. It was popularised in the 1960s with the release of 
        Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing 
        software like Aldus PageMaker including versions of Lorem Ipsum.";

        $result = $this->string->markovGenerator($txt, 100);
    }*/
}