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
    protected $brain;
    protected $helper;

    public function __construct(Brain $brain, Helper $helper)
    {
        $this->brain = $brain;
        $this->helper = $helper;
    }

    /**
     * Give answer to user input
     * @param $userInput
     * @return string
     */
    public function answer($userInput)
    {
        return $this->brain->getAnswer($userInput);
    }
}