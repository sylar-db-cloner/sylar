<?php

declare(strict_types=1);

namespace App;

use App\Infrastructure\CompilerPass\ConsoleCommandFilterCompilerPass;
use App\Infrastructure\CompilerPass\GraphQLResolverCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
}
