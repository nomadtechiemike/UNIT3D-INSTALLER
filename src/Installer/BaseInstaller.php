<?php

namespace App\Installer;

use App\Classes\Config;
use App\Classes\Process;
use App\Traits\ConsoleTools;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseInstaller
{
    use ConsoleTools;

    /**
     * @var SymfonyStyle $io
     */
    protected $io;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var $pkg_manager
     */
    protected $pkg_manager;

    /**
     * @var int $timeout
     */
    protected $timeout = 15;

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
        $this->input = $input;
        $this->output = $output;
        $this->config = $config;

        $this->process = new Process($io, $input, $output);
        $this->pkg_manager = $config->get('os.'.distname().'.pkg_manager');
    }

    abstract public function handle();

    protected function install($pkgs)
    {
        $this->process->execute($this->pkg_manager . " install -y $pkgs");
    }

    protected function process(array $commands, $force = false)
    {
        foreach ($commands as $cmd) {
            $this->process->execute($cmd, $force);
        }
    }

    protected function createFromStub(array $fr, $stub, $to)
    {
        $stub = resource_path() . distname() . '/' . $stub;

        $file = file_get_contents($stub);

        if ($file === false) {
            $this->throwError("'$stub' error getting file contents. Please report this bug.");
        }

        $contents = str_replace(array_keys($fr), array_values($fr), $file);

        file_put_contents($to, $contents);
        return true;
    }

    protected function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

}