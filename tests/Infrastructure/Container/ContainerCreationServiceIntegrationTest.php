<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Container;

use App\Core\ServiceCloner\Configuration\ConfigurationServiceInterface;
use App\Infrastructure\Docker\ContainerCreationServiceInterface;
use App\Infrastructure\Docker\ContainerExecServiceInterface;
use App\Infrastructure\Docker\ContainerFinderServiceInterface;
use App\Infrastructure\Docker\ContainerParameter\ContainerParameterDTO;
use App\Infrastructure\Process\ProcessInterface;
use Docker\API\Model\ContainerSummaryItem;
use Ramsey\Uuid\Uuid;
use Tests\AbstractIntegrationTest;

/**
 * @internal
 */
final class ContainerCreationServiceIntegrationTest extends AbstractIntegrationTest
{
    private ConfigurationServiceInterface $configurationService;
    private ContainerCreationServiceInterface $containerCreationService;
    private ContainerFinderServiceInterface $containerFinderService;
    private ProcessInterface $sudoProcess;
    private ContainerExecServiceInterface $containerExecService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sudoProcess = $this->getService(ProcessInterface::class);
        $this->configurationService = $this->getService(ConfigurationServiceInterface::class);
        $this->containerCreationService = $this->getService(ContainerCreationServiceInterface::class);
        $this->containerFinderService = $this->getService(ContainerFinderServiceInterface::class);
        $this->containerExecService = $this->getService(ContainerExecServiceInterface::class);
        $this->cleanExistingDockers();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanExistingDockers();
    }

    /**
     * @test
     */
    public function it_should_create_a_container_from_config(): void
    {
        $dockerName = 'unit-test-' . Uuid::uuid4()->toString();
        $this->sudoProcess->run(sprintf('echo "%s" > /tmp/%s', $dockerName, $dockerName));
        $config = $this->configurationService->createServiceFromArray([
            'name' => 'mini-webserver',
            'image' => 'tobilg/mini-webserver:0.5.1',
            'environment' => [[
              'name' => 'ENV_VARIABLE_1',
              'value' => 'ENV_VALUE_1',
            ]],
            'labels' => [[
              'name' => 'environment',
              'value' => 'unit-test',
            ]],
            'ports' => [[
              'hostIp' => '0.0.0.0',
              'hostPort' => '8198/tcp',
              'containerPort' => '3000/tcp',
            ]],
            'mounts' => [[
              'source' => '/tmp',
              'target' => '/app/tmp',
            ]],
        ]);

        $this->containerCreationService->createDocker(
            new ContainerParameterDTO(
                $dockerName,
                0,
                '/tmp'
            ),
            $config,
            ['sylar-label' => '1']
        );

        sleep(2);

        /** @var ContainerSummaryItem $containerSummaryItem */
        $containerSummaryItem = $this->containerFinderService->getDockerByName($dockerName);

        self::assertNotNull($containerSummaryItem);
        self::assertSame('unit-test', $containerSummaryItem->getLabels()['environment']);
        self::assertSame('running', $containerSummaryItem->getState());
        self::assertSame('node /app/mini-webserver.js', $containerSummaryItem->getCommand());
        self::assertCount(1, $containerSummaryItem->getPorts());
        $port = $containerSummaryItem->getPorts()[0];
        self::assertSame('0.0.0.0', $port->getIP());
        self::assertSame(8198, $port->getPublicPort());
        self::assertSame(3000, $port->getPrivatePort());
        self::assertSame('tcp', $port->getType());
        self::assertArrayHasKey('sylar-label', $containerSummaryItem->getLabels());
        self::assertSame('1', $containerSummaryItem->getLabels()['sylar-label']);
        self::assertSame($dockerName, trim($this->sudoProcess->run(sprintf('cat /tmp/%s', $dockerName))->getStdOutput()));
        self::assertSame($dockerName, trim($this->containerExecService->exec($dockerName, 'cat', '/app/tmp/' . $dockerName)));
        $this->containerExecService->exec($dockerName, 'sh', '-c', 'rm /app/tmp/' . $dockerName);
        self::assertSame('', $this->sudoProcess->run(sprintf('ls "/tmp/%s" || true', $dockerName))->getStdOutput());
    }

    private function cleanExistingDockers(): void
    {
        $this->sudoProcess->mayRun('docker', 'rm', '--force', '$(docker ps --filter "label=environment=unit-test" -a --format "{{.ID}}")');
    }
}
