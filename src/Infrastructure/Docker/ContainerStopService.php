<?php

declare(strict_types=1);
declare(ticks=1);

namespace App\Infrastructure\Docker;

use Docker\Docker;

final class ContainerStopService implements ContainerStopServiceInterface
{
    private Docker $docker;
    private ContainerFinderServiceInterface $dockerFinderService;

    public function __construct(
        Docker $dockerReadWrite,
        ContainerFinderServiceInterface $dockerFinderService
    ) {
        $this->docker = $dockerReadWrite;
        $this->dockerFinderService = $dockerFinderService;
    }

    public function stop(string $dockerName, string ...$arguments): void
    {
        $container = $this->dockerFinderService->getDockerByName($dockerName);
        if ($container === null) {
            return;
        }

        $this->docker->containerStop($container->getId());
    }
}
