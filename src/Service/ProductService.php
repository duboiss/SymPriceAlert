<?php

declare(strict_types=1);

namespace App\Service;

use App\Event\ProductParsedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ProductService
{
    private string $filePath = '';
    private ?array $dataProducts = null;

    public function __construct(private HttpClientInterface $httpClient, private LoggerInterface $logger, private NotifyService $notifyService, private EventDispatcherInterface $dispatcher)
    {
    }

    public function analyse(SplFileInfo $file): void
    {
        $this->filePath = $file->getRealPath();
        $this->dataProducts = $this->getDataProducts();
        $crawlerSelector = $this->dataProducts['selector'];
        $products = $this->dataProducts['products'];

        foreach ($products as $index => $product) {
            sleep(5);
            if (!$response = $this->getResponse($product)) {
                continue;
            }

            $crawler = new Crawler($response->getContent());
            $selector = $crawler->filter($crawlerSelector);

            if (!$selector->count()) {
                $this->logger->error(sprintf(
                    'Selector not found for "%s" product (%s)',
                    $product['title'],
                    $file->getFilename())
                );

                continue;
            }

            $formattedPrice = $selector->text();

            $this->dispatcher->dispatch(new ProductParsedEvent($product, $formattedPrice));

            $price = (int) str_replace(',', '', $selector->text());

            $this->checkProductPrice($product, $price, $index, $formattedPrice);
        }

        $this->updateDataProducts();
    }

    private function getDataProducts(): array
    {
        /** @var string $jsonContent */
        $jsonContent = file_get_contents($this->filePath);

        try {
            return json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException("Failure during decoding of {$this->filePath}.json file: {$e}");
        }
    }

    private function getResponse(array $product): ?ResponseInterface
    {
        try {
            $response = $this->httpClient->request('GET', $product['url']);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException("Failure during the request {$product['url']}: {$e}");
        }

        if (($statusCode = $response->getStatusCode()) !== 200) {
            $this->logger->error(sprintf(
                'Unable to check product "%s" (%s) : status code %d.',
                $product['title'],
                $product['url'],
                $statusCode
            ));

            return null;
        }

        return $response;
    }

    private function checkProductPrice(array $product, int $price, int $index, string $formattedPrice): void
    {
        if ($price <= ($product['desiredPrice'] * 100) && (!array_key_exists('alertedPrice', $product) || $price < $product['alertedPrice'])) {
            $this->updateProductAlertedPrice($index, $price);

            $this->notifyService->notify($product, $formattedPrice);
            $this->notifyService->sendLog($product, $formattedPrice);
        }
    }

    private function updateProductAlertedPrice(int $productIndex, int $price): void
    {
        $this->dataProducts['products'][$productIndex]['alertedPrice'] = $price;
    }

    private function updateDataProducts(): void
    {
        try {
            file_put_contents($this->filePath, json_encode($this->dataProducts, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        } catch (\JsonException $e) {
            throw new RuntimeException("Failure during encoding of {$this->filePath}.json file: {$e}");
        }
    }
}
