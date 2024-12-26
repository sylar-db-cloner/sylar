<?php

declare(strict_types=1);

namespace App;

use App\Infrastructure\CompilerPass\ConsoleCommandFilterCompilerPass;
use App\Infrastructure\CompilerPass\GraphQLResolverCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function build(ContainerBuilder $container): void
    {
        if ($this->environment === 'prod') {
            $container->addCompilerPass(new ConsoleCommandFilterCompilerPass());
        }
        $container->addCompilerPass(new GraphQLResolverCompilerPass());
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/{packages}/*.{php,yaml}');
        $container->import($configDir . '/{packages}/' . $this->environment . '/*.{php,yaml}');
        $container->import($configDir . '/services/*.{php,yaml}');
        $container->import($configDir . '/services/' . $this->environment . '/*.{php,yaml}');

        if (is_file($configDir . '/services.yaml')) {
            $container->import($configDir . '/services.yaml');
            $container->import($configDir . '/{services}_' . $this->environment . '.yaml');
        } else {
            $container->import($configDir . '/{services}.php');
            $container->import($configDir . '/{services}_' . $this->environment . '.php');
        }
    }
}
