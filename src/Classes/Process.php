<?php

namespace App\Classes;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{

    /**
     * @var SymfonyStyle $io
     */
    protected $io;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Process constructor.
     * @param SymfonyStyle $io
     */
    public function __construct(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $this->io = $io;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Executes a new Process instance
     *
     * @param string $cmd
     * @return string
     */
    public function execute($command, $force = false)
    {
        $optionValue = $this->input->getOption('debug');
        $debug = ($optionValue !== false);

        $this->io->writeln("\n<fg=cyan>$command</>");

        $process = new SymfonyProcess($command, null, null, null, 3600);
        $process->setIdleTimeout(360);

        $process->start();

        $bar = null;
        if (!$debug) {
            $bar = $this->progressStart();
        }

        $process->wait(function ($type, $buffer) use ($bar, $debug) {
            if ($debug) {
                $this->io->writeln("[OUTPUT] $buffer");
            } else {
                $bar->advance();
                usleep(200000);
            }
        });

        $debug ?: $this->progressStop($bar);
        $process->stop();

        if (!$process->isSuccessful()) {
            if (!$force) {
                $this->io->error($process->getErrorOutput());
                die();
            }

            $this->io->writeln("\n<fg=red>[Warning]</> " . $process->getErrorOutput());
        }

        return $process;
    }

    /**
     * @return ProgressBar
     */
    protected function progressStart()
    {
        $bar = $this->io->createProgressBar();
        $bar->setBarCharacter('<fg=magenta>=</>');
        $bar->setFormat('[%bar%] (<fg=cyan>%message%</>)');
        $bar->setMessage('Please Wait ...');
        //$bar->setRedrawFrequency(20); todo: may be useful for platforms like CentOS
        $bar->start();

        return $bar;
    }

    /**
     * @param $bar
     */
    protected function progressStop(ProgressBar $bar)
    {
        $bar->setMessage("<fg=green>Done!</>");
        $bar->finish();
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }
}