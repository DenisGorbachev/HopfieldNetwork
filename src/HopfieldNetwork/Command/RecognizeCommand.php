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
            ->addOption('seed', 's', InputOption::VALUE_REQUIRED, 'A number to seed the mt_rand generator', mt_rand())
            ->addArgument('patterns-file', InputArgument::REQUIRED, 'A file containing patterns this network is going to recognize')
            ->addArgument('inputs-file', InputArgument::REQUIRED, 'A file containing data this network is going to recognize as patterns')
    ;}

    protected function execute(InputInterface $input, OutputInterface $output) {
        $patterns = $this->readMatricesAsVectors($input->getArgument('patterns-file'));
        $inputs = $this->readMatricesAsVectors($input->getArgument('inputs-file'));
        $arity = count($patterns[0]);
        $weights = array_fill(0, $arity, array_fill(0, $arity, 0));
        for ($x = 0; $x < $arity; $x++) {
            for ($y = $x; $y < $arity; $y++) {
                if ($x != $y) { // otherwise it's already 0
                    $sum = 0;
                    foreach ($patterns as $pattern) {
                        $sum += (2*$pattern[$x] - 1)*(2*$pattern[$y] - 1);
                    }
                    $weights[$x][$y] = $sum;
                    $weights[$y][$x] = $sum;
                }
            }
        }

        $output->writeln('Patterns matrix:');
        $this->printMatrix($patterns);
        $output->writeln('');

        $output->writeln('Weigth matrix:');
        $this->printMatrix($weights);
        $output->writeln('');

        mt_srand($input->getOption('seed'));

        foreach ($inputs as $patternToBe) {
            $output->writeln('Recognizing');
            $this->printMatrix(array($patternToBe));
            do {
                $updated = false;
                $indexes = range(0, $arity-1);
                for ($n = 0; $n < $arity; $n++) {
                    $relativeIndex = mt_rand(0, count($indexes)-1);
                    $index = $indexes[$relativeIndex];
                    unset($indexes[$relativeIndex]);
                    sort($indexes);
                    $oldValue = $patternToBe[$index];
                    $sum = 0;
                    foreach ($weights[$index] as $j=>$weight) {
                        $sum += $patternToBe[$j]*$weight;
                    }
                    $patternToBe[$index] = ($sum >= 0)? 1 : 0;
                    $updated = $oldValue != $patternToBe[$index];
                }
            } while ($updated);
            $output->writeln('as');
            $this->printMatrix(array($patternToBe));
            $output->writeln('');
        }
    }

    protected function readMatricesAsVectors($filename)
    {
        $vectors = array(array());
        $fp = fopen($filename, 'r');
        while ($line = fgets($fp)) {
            $matches = array();
            preg_match_all('/1|0/', $line, $matches, PREG_PATTERN_ORDER);
            $matches = $matches[0];
            if (empty($matches)) {
                $vectors[] = array();
            } else {
                $vectors[count($vectors) - 1] = array_merge($vectors[count($vectors) - 1], $matches);
            }
        }
        if (empty($vectors[count($vectors)-1])) {
            unset($vectors[count($vectors)-1]);
        }
        return $vectors;
    }

    public function printMatrix($matrix) {
        foreach ($matrix as $row) {
            foreach ($row as $value) {
                echo sprintf('%2d', $value).' ';
            }
            echo PHP_EOL;
        }
    }
}
