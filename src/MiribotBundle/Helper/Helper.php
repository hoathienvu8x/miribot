<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 22-Aug-17
 * Time: 12:03
 */

namespace MiribotBundle\Helper;

class Helper
{
    public $string;
    public $memory;
    public $template;

    public function __construct(MemoryHelper $memory, StringHelper $string, TemplateHelper $template)
    {
        $this->string = $string;
        $this->memory = $memory;
        $this->template = $template;
    }
}