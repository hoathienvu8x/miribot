<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 20-Aug-17
 * Time: 11:39
 */

namespace MiribotBundle\Model;

use MiribotBundle\Helper\Helper;

class Miribot
{
    const BOT_ALIAS = "miri";

    protected $brain;
    protected $helper;

    public function __construct(Brain $brain, Helper $helper)
    {
        $this->brain = $brain;
        $this->helper = $helper;
        $this->init();
    }

    protected function init()
    {

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