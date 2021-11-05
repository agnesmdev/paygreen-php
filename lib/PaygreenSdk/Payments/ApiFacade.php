<?php

namespace Paygreen\Sdk\Payments;

use Exception;
use Paygreen\Sdk\Core\Components\Environment;
use Paygreen\Sdk\Payments\Exceptions\InvalidApiVersion;
use Http\Client\HttpClient as HttpClientInterface;
use Paygreen\Sdk\Core\Logger;
use Paygreen\Sdk\Payments\Components\Builders\RequestBuilder;
use Paygreen\Sdk\Payments\Exceptions\PaymentCreationException;
use Paygreen\Sdk\Payments\Interfaces\OrderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Http\Client\Exception as HttpClientException;

class ApiFacade
{
    /** @var HttpClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    /** @var RequestBuilder */
    private $requestBuilder;

    /** @var Environment */
    private $environment;

    /**
     * @param HttpClientInterface $client
     * @param LoggerInterface|null $logger
     * @throws InvalidApiVersion
     */
    public function __construct(HttpClientInterface $client, LoggerInterface $logger = null)
    {
        $this->client = $client;

        if ($logger === null) {
            $this->logger = new Logger('api.payment');
        } else {
            $this->logger = $logger;
        }

        $this->environment = new Environment(
            getenv('PG_PAYMENT_API_PUBLIC_KEY'),
            getenv('PG_PAYMENT_API_PRIVATE_KEY'),
            getenv('PG_PAYMENT_API_SERVER'),
            getenv('PG_PAYMENT_API_VERSION')
        );

        $this->requestBuilder = new RequestBuilder($this->environment);
    }

    /**
     * @param OrderInterface $order
     * @param int $amount
     * @param string $notifiedUrl
     * @param string $paymentType
     * @param string $currency
     * @param string $returnedUrl
     * @param array $metadata
     * @param array $eligibleAmount
     * @param string $ttl
     * @return mixed|void
     * @throws PaymentCreationException
     * @throws Exception
     */
    public function createCash(
        OrderInterface $order,
        $amount,
        $notifiedUrl,
        $paymentType = 'CB',
        $currency = 'EUR',
        $returnedUrl = '',
        $metadata = [],
        $eligibleAmount = [],
        $ttl = ''
    ) {
        $this->logger->info("Create '$paymentType' cash payment with an amount of '$amount'.");

        $request = $this->requestBuilder->buildRequest(
            'create_cash',
            [
                'ui' => $this->environment->getPublicKey()
            ],
            [
                'orderId' => 'PG-' . $order->getReference(),
                'amount' => $amount,
                'currency' => $currency,
                'paymentType' => $paymentType,
                'notifiedUrl' => $notifiedUrl,
                'returnedUrl' => $returnedUrl,
                'buyer' => (object) [
                    'id' => $order->getCustomer()->getId(),
                    'lastName' => $order->getCustomer()->getLastName(),
                    'firstName' => $order->getCustomer()->getFirstName(),
                    'country' => $order->getCustomer()->getCountryCode()
                ],
                'shippingAddress' => (object) [
                    'lastName' => $order->getShippingAddress()->getLastName(),
                    'firstName' => $order->getShippingAddress()->getFirstName(),
                    'address' => $order->getShippingAddress()->getStreet(),
                    'zipCode' => $order->getShippingAddress()->getZipCode(),
                    'city' => $order->getShippingAddress()->getCity(),
                    'country' => $order->getShippingAddress()->getCountryCode()
                ],
                'billingAddress' => (object) [
                    'lastName' => $order->getBillingAddress()->getLastName(),
                    'firstName' => $order->getBillingAddress()->getFirstName(),
                    'address' => $order->getBillingAddress()->getStreet(),
                    'zipCode' => $order->getBillingAddress()->getZipCode(),
                    'city' => $order->getBillingAddress()->getCity(),
                    'country' => $order->getBillingAddress()->getCountryCode()
                ],
                'metadata' => $metadata,
                'eligibleAmount' => $eligibleAmount,
                'ttl' => $ttl
            ]
        );

        /** @var ResponseInterface $response */
        $response = $this->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new PaymentCreationException(
                "An error occurred while creating a payment task for order '{$order->getReference()}'."
            );
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    private function sendRequest(RequestInterface $request)
    {
        try {
            $this->logger->info("Sending request '{$request->getUri()->getPath()}' ");

            /** @var ResponseInterface $response */
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() >= 400) {
                $this->logger->error('Request error : ', [
                    'code' => $response->getStatusCode(),
                    'reasonPhrase' => $response->getReasonPhrase()
                ]);
            }

            return $response;
        } catch (HttpClientException $exception) {
            $this->logger->error("An error occurred while sending request.", [$exception]);
        }
    }
}