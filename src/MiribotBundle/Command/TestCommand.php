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
        //$this->whatever();
        echo array_sum(array());
    }

    protected function whatever()
    {
        $txt = "The unicorn is a legendary creature that has been described since antiquity as a beast with a single large, pointed, spiraling horn projecting from its forehead. The unicorn was depicted in ancient seals of the Indus Valley Civilization and was mentioned by the ancient Greeks in accounts of natural history by various writers, including Ctesias, Strabo, Pliny the Younger, and Aelian. The Bible also describes an animal, the re'em, which some versions translate as unicorn. In European folklore, the unicorn is often depicted as a white horse-like or goat-like animal with a long horn and cloven hooves (sometimes a goat's beard). In the Middle Ages and Renaissance, it was commonly described as an extremely wild woodland creature, a symbol of purity and grace, which could only be captured by a virgin. In the encyclopedias its horn was said to have the power to render poisoned water potable and to heal sickness. In medieval and Renaissance times, the tusk of the narwhal was sometimes sold as unicorn horn.";
        $order = 20;
        $ngrams = array();
        $gramFreq = array();

        for ($i = 0; $i <= strlen($txt) - $order; $i++) {
            $gram = substr($txt, $i, $order);

            if (strlen($gram) < $order) {
                $gram .= str_repeat(" ", $order - strlen($gram));
            }

            if (!isset($gramFreq[$gram])) {
                $gramFreq[$gram] = array();
            }

            if (isset($txt{$i + $order})) {
                $gramFreq[$gram][] = $txt{$i + $order};
            }

            $ngrams[] = $gram;
        }

        //var_dump($gramFreq);
        echo $this->markovIt($txt, $order, $gramFreq);
    }

    protected function markovIt($txt, $order, $gramFreq)
    {
        $currentGram = substr($txt, 0, $order);
        $result = $currentGram;

        for ($i = 0; $i < 100; $i++) {
            if (isset($gramFreq[$currentGram])) {
                $possibilities = $gramFreq[$currentGram];
                if (!empty($possibilities)) {
                    $nextIdx = array_rand($possibilities);
                    $result .= $possibilities[$nextIdx];
                    $currentGram = substr($result, strlen($result) - $order, $order);
                }
            } else {
                break;
            }
        }

        return $result;
    }
}
