<?php

declare(strict_types=1);

namespace Tests\Core\ServiceCloneService;

use App\Core\ServiceCloner\Configuration\ConfigurationService;
use App\Core\ServiceCloner\ServiceClonerArrayDumperService;
use App\Infrastructure\Docker\ContainerParameter\ConfigurationExpressionGenerator;
use App\Infrastructure\Docker\ContainerParameter\ContainerParameterDTO;
use Tests\AbstractIntegrationTestCase;

/**
 * @internal
 *
 * @group integration
 */
final class ServiceClonerArrayDumperServiceIntegrationTest extends AbstractIntegrationTestCase
{
    /**
     * @test
     */
    public function it_should_do_get_an_array_representing_a_configuration(): void
    {
        preg_match('!/tests/(.*)!', __DIR__, $matches);
        $testConfigurationName = 'start_master';
        $configurationService = new ConfigurationService(
            sprintf('%s/data/%s/sylar.yaml', __DIR__, $testConfigurationName),
            sprintf('%s/tests/%s/data/%s', getenv('MOUNTED_CONFIGURATION_PATH'), $matches[1], $testConfigurationName),
            sprintf('%s/tests/%s/data/%s', getenv('CONTAINER_CONFIGURATION_PATH'), $matches[1], $testConfigurationName),
        );
        $configurationExpressionGenerator = new ConfigurationExpressionGenerator($configurationService);
        $serviceClonerCommandLineDumperService = new ServiceClonerArrayDumperService($configurationService, $configurationExpressionGenerator);
        $containerParameter = new ContainerParameterDTO(
            'mysql-test',
            0,
            'toto/tata',
        );

        self::assertSame($this->getExpectedLifecycleHooksArray(), $serviceClonerCommandLineDumperService->dump($containerParameter));
    }

    private function getExpectedLifecycleHooksArray(): array
    {
        $mounted_configuration_path = getenv('MOUNTED_CONFIGURATION_PATH');

        return json_decode(<<<EOJ
                        {
                        "configurationRoot": "{$mounted_configuration_path}/tests/Core/ServiceCloneService/data/start_master",
                        "stateRoot": "/app/data",
                        "zpoolName": "sylar",
                        "zpoolRoot": "/sylar",
                        "services": [
                            {
                                "name": "unit-test-mysql-start-master",
                                "image": "library/mariadb:10.5.3",
                                "command": "",
                                "entryPoint": null,
                                "networkMode": "n1",
                                "lifeCycleHooks": {
                                    "preStartCommands": [
                                        {
                                            "executionEnvironment": "host",
                                            "command": [
                                                "ls",
                                                "/"
                                            ]
                                        }
                                    ],
                                    "postStartWaiters": [
                                        {
                                            "type": "logMatch",
                                            "expression": "!Server socket created on IP!",
                                            "timeout": 30
                                        }
                                    ],
                                    "postStartCommands": [
                                        {
                                            "executionEnvironment": "host",
                                            "command": [
                                                "ls",
                                                "/"
                                            ]
                                        }
                                    ],
                                    "postDestroyCommands": [
                                        {
                                            "executionEnvironment": "host",
                                            "command": [
                                                "ls",
                                                "/"
                                            ]
                                        }
                                    ]
                                },
                                "environments": [
                                    {
                                        "name": "MYSQL_ROOT_PASSWORD",
                                        "value": "root_password"
                                    },
                                    {
                                        "name": "MYSQL_USER",
                                        "value": "user"
                                    },
                                    {
                                        "name": "MYSQL_PASSWORD",
                                        "value": "password"
                                    },
                                    {
                                        "name": "MYSQL_DATABASE",
                                        "value": "roketto"
                                    },
                                    {
                                        "name": "MYSQL_INITDB_SKIP_TZINFO",
                                        "value": "1"
                                    },
                                    {
                                        "name": "CLONE_NAME",
                                        "value": "mysql-test"
                                    },
                                    {
                                        "name": "CLONE_INDEX",
                                        "value": "0"
                                    },
                                    {
                                        "name": "CLONE_REPLICATED_FILESYSTEM",
                                        "value": "toto/tata"
                                    }
                                ],
                                "mounts": [{
                                    "source": "toto/tata",
                                    "target": "/var/lib/mysql"
                                },{
                                    "source": "{$mounted_configuration_path}/tests/Core/ServiceCloneService/data/start_master",
                                    "target": "/app"
                                },{
                                    "source": "{$mounted_configuration_path}/tests/Core/ServiceCloneService/data/start_master/mysql/etc/mysql/conf.d",
                                    "target": "/etc/mysql/conf.d"
                                }],
                                "ports": [{
                                    "containerPort": "3306/tcp",
                                    "hostPort": "3406/tcp",
                                    "hostIp": "0.0.0.0"
                                }],
                                "labels": [
                                    {
                                        "name": "environment",
                                        "value": "unit-test"
                                    }
                                ]
                            }
                        ]
                    }
            EOJ, true);
    }
}
