<?php

declare(strict_types=1);

namespace App\Infrastructure\Docker;

use Docker\Docker;
use Docker\DockerClientFactory;
use Http\Client\Common\Plugin\LoggerPlugin;
use Http\Client\Common\PluginClient;

final class DockerFactory implements DockerFactoryInterface
{
    private DockerApiLogger $dockerApiLogger;

    public function __construct(
        DockerApiLogger $dockerApiLogger
    ) {
        $this->dockerApiLogger = $dockerApiLogger;
    }

    public function create(string $dockerRemoteSocket): Docker
    {
        $httpClient = new PluginClient(DockerClientFactory::create([
            'remote_socket' => $dockerRemoteSocket,
        ]), [
            new LoggerPlugin($this->dockerApiLogger),
        ]);
        /* @phpstan-ignore-next-line */
        return Docker::create($httpClient);
    }
}