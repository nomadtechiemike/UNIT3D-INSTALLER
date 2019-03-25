<?php

namespace App\Installer\Policies;

use App\Classes\Config;
use App\Traits\ConsoleTools;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BasePolicy
{
    use ConsoleTools;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(SymfonyStyle $io, Config $config, InputInterface $input, OutputInterface $output)
    {
        $this->io = $io;
        $this->config = $config;
        $this->input = $input;
        $this->output = $output;
    }

    public function handle($param = null)
    {
        $this->allows($param);
    }

    abstract public function allows($param = null);
}