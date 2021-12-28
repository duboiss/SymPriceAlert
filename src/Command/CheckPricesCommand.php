<?php

declare(strict_types=1);

namespace App\Command;

use App\Event\ProductParsedEvent;
use App\Service\ProductService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand('app:prices:check', description: 'Check all prices in urls provided by DataProducts directory')]
class CheckPricesCommand extends Command
{
    private const PRODUCTS_PATH = __DIR__ . '/../DataProducts';

    public function __construct(private readonly ProductService $productService, private readonly EventDispatcherInterface $dispatcher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dispatcher->addListener(ProductParsedEvent::class, function (ProductParsedEvent $event) use ($output): void {
            $product = $event->getProduct();
            $output->writeln(sprintf(
                '%s (%s): %s € / %s',
                $product['title'],
                $product['url'],
                $product['desiredPrice'],
                $event->getFormattedPrice()
            ));
        });

        $finder = new Finder();
        $finder->files()->in(self::PRODUCTS_PATH);

        foreach ($finder as $file) {
            $this->productService->analyse($file);
        }

        return Command::SUCCESS;
    }
}
