<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    /** @var array<array{component: string, dimension: string, needed: float, available: float}> */
    private array $shortages;

    public function __construct(array $shortages)
    {
        $this->shortages = $shortages;

        $lines = array_map(
            fn ($s) => "{$s['component']} ({$s['dimension']}): საჭიროა {$s['needed']}, ნაშთი {$s['available']}",
            $shortages
        );

        parent::__construct(implode("\n", $lines));
    }

    public function getShortages(): array
    {
        return $this->shortages;
    }
}
