<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 20-Aug-17
 * Time: 11:59
 */

namespace MiribotBundle\Command;


use ChrisKonnertz\StringCalc\StringCalc;
use MiribotBundle\Helper\StringHelper;
use MiribotBundle\Model\Miribot;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('miribot:test')
            ->setDescription('Test a function of Miribot.')
            ->setHelp('Nope!!!');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $string = "This is 1.2 + 1 and + sin(123) expression math and * abs(-4) yeah";
        $term = $this->getContainer()->get('helper_string')->produceMathExpression($string);
        echo $term . "\n";
        $strCalc = new StringCalc();
        echo $strCalc->calculate($term);
    }
}
