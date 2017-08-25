<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 20-Aug-17
 * Time: 11:59
 */

namespace MiribotBundle\Command;


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
            ->setHelp('Nope!!!')
            ->addArgument('input', InputArgument::REQUIRED, 'User input to the bot');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $aiml = new \DOMDocument();
        $aiml->load("E:\\Projects\\miribot\\aiml\\core_test.aiml");
        $patterns = $aiml->getElementsByTagName("pattern");
        $input = "KHUE IS GOOD";
        foreach ($patterns as $pattern) {
            $match = array();
            preg_match_all($input, $pattern, $match);
            var_dump($match);
        }
    }
}
