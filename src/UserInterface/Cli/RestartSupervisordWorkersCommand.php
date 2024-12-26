<?php

declare(strict_types=1);

namespace App\UserInterface\Cli;

use fXmlRpc\Client as fXmlRpcClient;
use fXmlRpc\Transport\PsrTransport;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Http\Adapter\Guzzle7\Client;
use Supervisor\Supervisor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('tools:restart-worker', description: 'restart supervisord process')]
final class RestartSupervisordWorkersCommand extends Command
{
    private Supervisor $supervisor;

    public function __construct(
        string $supervisordUrl,
        string $supervisordUser,
        string $supervisordPassword,
    ) {
        parent::__construct();
        $this->supervisor = new Supervisor(new fXmlRpcClient(
            $supervisordUrl,
            new PsrTransport(
                new HttpFactory(),
                new Client(new GuzzleHttpClient([
                    'auth' => [$supervisordUser, $supervisordPassword],
                ])),
            ),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->supervisor->stopProcessGroup('php-worker', true);
        $this->supervisor->startProcessGroup('php-worker', true);

        return 0;
    }
}
