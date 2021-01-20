<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

/**
 *
 */

declare(strict_types=1);

namespace Affiliation\ValueObject;

use function sprintf;

final class PaymentSheetPeriod
{
    private int $year;
    private int $period;

    public function __construct(int $year, int $period)
    {
        $this->year = $year;
        $this->period = $period;
    }

    public function getId(): string
    {
        return sprintf('payment-sheet-%s-%sh', $this->year, $this->period);
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function getLabel(): string
    {
        return sprintf('%s-%sH', $this->year, $this->period);
    }

    public function isActive(): bool
    {
        return $this->year === (int)date('Y') && $this->period === (date('m') <= 6 ? 1 : 2);
    }
}
