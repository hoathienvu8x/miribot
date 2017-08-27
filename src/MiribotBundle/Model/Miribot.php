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
        $path = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . self::BOT_ALIAS . ".json";
        $json = @file_get_contents($path);
        if ($json && $properties = json_decode($json, true)) {
            foreach ($properties as $property => $value) {
                $this->helper->memory->rememberUserData("bot.{$property}", $value);
            }
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