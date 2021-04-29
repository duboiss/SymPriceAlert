<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The PARSED event occurs once a product was parsed.
 *
 * This event allows you to run expensive post-parse jobs.
 */
class ProductParsedEvent extends Event
{
    public function __construct(protected array $product, protected string $formattedPrice)
    {
    }

    public function getProduct(): array
    {
        return $this->product;
    }

    public function getFormattedPrice(): string
    {
        return $this->formattedPrice;
    }
}
