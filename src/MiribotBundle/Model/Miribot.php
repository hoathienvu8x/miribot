<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 20-Aug-17
 * Time: 11:39
 */

namespace MiribotBundle\Model;

use MiribotBundle\Helper\Helper;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Serializer\Encoder\JsonDecode;

class Miribot
{
    const BOT_ALIAS = "miri";
    const DS = DIRECTORY_SEPARATOR;

    protected $kernel;
    protected $brain;
    protected $helper;

    public function __construct(Kernel $kernel, Brain $brain, Helper $helper)
    {
        $this->brain = $brain;
        $this->helper = $helper;
        $this->kernel = $kernel;
        $this->init();
    }

    /**
     * Initialize Miri's identity
     */
    protected function init()
    {
        // Load Miri's predefined properties onto memory
        $path = $this->kernel->getProjectDir() . self::DS . 'core' . self::DS . 'system' . self::DS . self::BOT_ALIAS . ".json";
        $json = @file_get_contents($path);
        $properties = json_decode($json, true);
        foreach ($properties as $property => $value) {
            $this->helper->memory->rememberUserData("bot.{$property}", $value);
        }
    }

    /**
     * Give answer to user input
     * @param $userInput
     * @return array
     */
    public function answer($userInput)
    {
        return $this->brain->getAnswer($userInput);
    }
}