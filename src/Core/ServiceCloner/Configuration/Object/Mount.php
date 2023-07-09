<?php

declare(strict_types=1);

namespace App\Core\ServiceCloner\Configuration\Object;

final class Mount
{
    private string $source;
    private string $target;

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    /** @internal */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /** @internal */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}
