<?php

namespace HopfieldNetwork\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RecognizeCommand extends Command
{
    protected function configure() {
        $this
            ->setName('recognize')
            ->setDescription('Recognize the pattern')
            ->addArgument('patterns-file', InputArgument::REQUIRED, 'A file containing patterns this network is going to recognize')
            ->addArgument('input-file', InputArgument::REQUIRED, 'A file containing data this network is going to recognize as pattern')
    ;}

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('hi');
    }
}
