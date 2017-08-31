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
        $path = $this->kernel->getContainer()->getParameter('path_bot_info');
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
        // Download learned file and chat log
        $this->downloadNecessaryFiles();

        // Get answer
        $answer = $this->brain->getAnswer($userInput);

        // Then upload updated file
        $this->uploadNecessaryFiles();

        return $answer;
    }

    /**
     * Download necessary files
     */
    protected function downloadNecessaryFiles()
    {
        // Check if learn file and chat log exist
        $learnPath = $this->kernel->getContainer()->getParameter('path_aiml_learn');
        $chatLogPath = $this->kernel->getContainer()->getParameter('path_chatlog');

        // Update changes from drop box
        $this->helper->downloadFromDropbox($learnPath);
        $this->helper->downloadFromDropbox($chatLogPath);
    }

    /**
     * Upload necessary files
     */
    protected function uploadNecessaryFiles()
    {
        $learnPath = $this->kernel->getContainer()->getParameter('path_aiml_learn');
        $chatLogPath = $this->kernel->getContainer()->getParameter('path_chatlog');

        // Upload changes to drop box
        $this->helper->uploadToDropbox($learnPath);
        $this->helper->uploadToDropbox($chatLogPath);
    }
}