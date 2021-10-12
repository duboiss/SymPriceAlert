<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class NotifyService
{
    public function __construct(private LoggerInterface $logger, private NotifierInterface $notifier, private $mailerTo, private $phoneNumber)
    {
    }

    public function notify(array $product, string $formattedPrice): void
    {
        $notification = (new Notification('Price alert'))
            ->importance(Notification::IMPORTANCE_HIGH)
            ->subject(sprintf('Price alert / %s - %s', $product['title'], $formattedPrice))
            ->content(sprintf(
                'Price alert for "%s" product : %s (%s€ desired) at url %s',
                $product['title'],
                $formattedPrice,
                $product['desiredPrice'],
                $product['url']
            ))
        ;

        $this->notifier->send($notification, new Recipient($this->mailerTo, $this->phoneNumber));
    }

    public function sendLog(array $product, string $price): void
    {
        $this->logger->notice(sprintf(
            'Price alert for "%s" (%s): %s € / %s',
            $product['title'],
            $product['url'],
            $product['desiredPrice'],
            $price
        ));
    }
}
