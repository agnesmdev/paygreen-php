<?php

namespace Paygreen\Sdk\Payment\V3\Request\Buyer;

use Exception;
use GuzzleHttp\Psr7\Request;
use Paygreen\Sdk\Core\Encoder\JsonEncoder;
use Paygreen\Sdk\Core\Exception\ConstraintViolationException;
use Paygreen\Sdk\Core\Normalizer\CleanEmptyValueNormalizer;
use Paygreen\Sdk\Core\Serializer\Serializer;
use Paygreen\Sdk\Core\Validator\Validator;
use Paygreen\Sdk\Payment\V3\Model\Buyer;
use Psr\Http\Message\RequestInterface;

class BuyerRequest extends \Paygreen\Sdk\Core\Request\Request
{
    /**
     * @return Request|RequestInterface
     * @throws Exception
     */
    public function getCreateRequest(Buyer $buyer)
    {
        $violations = Validator::validateModel($buyer);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $publicKey = $this->environment->getPublicKey();

        $body = [
            'email' => $buyer->getEmail(),
            'first_name' => $buyer->getFirstname(),
            'last_name' => $buyer->getLastname(),
            'reference' => $buyer->getId(),
            'country' => $buyer->getCountryCode(),
            'billing_address' => [
                'line1' => $buyer->getBillingAddress()->getStreetLineOne(),
                'line2' => $buyer->getBillingAddress()->getStreetLineTwo(),
                'city' => $buyer->getBillingAddress()->getCity(),
                'postal_code' => $buyer->getBillingAddress()->getPostalCode(),
                'country' => $buyer->getBillingAddress()->getCountryCode(),
            ]
        ];

        return $this->requestFactory->create(
            "/payment/shops/{$publicKey}/buyers",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->isJson()->getRequest();
    }

    /**
     * @return Request|RequestInterface
     */
    public function getGetRequest(Buyer $buyer)
    {
        $violations = Validator::validateModel($buyer, "reference");

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $publicKey = $this->environment->getPublicKey();
        $buyerReference = $buyer->getReference();

        return $this->requestFactory->create(
            "/payment/shops/{$publicKey}/buyers/{$buyerReference}",
            null,
            'GET'
        )->withAuthorization()->isJson()->getRequest();
    }

    /**
     * @return Request|RequestInterface
     */
    public function getUpdateRequest(Buyer $buyer)
    {
        $violations = Validator::validateModel($buyer, "reference");

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $publicKey = $this->environment->getPublicKey();
        $buyerReference = $buyer->getReference();

        $body = [
            'email' => $buyer->getEmail(),
            'first_name' => $buyer->getFirstname(),
            'last_name' => $buyer->getLastname(),
            'reference' => $buyer->getId(),
            'country' => $buyer->getCountryCode()
        ];

        if (null !== $buyer->getBillingAddress()) {
            $body [] = [
                'billing_address' => [
                    'line1' => $buyer->getBillingAddress()->getStreetLineOne(),
                    'line2' => $buyer->getBillingAddress()->getStreetLineTwo(),
                    'city' => $buyer->getBillingAddress()->getCity(),
                    'postal_code' => $buyer->getBillingAddress()->getPostalCode(),
                    'country' => $buyer->getBillingAddress()->getCountryCode()
                ]
            ];
        }

        return $this->requestFactory->create(
            "/payment/shops/{$publicKey}/buyers/{$buyerReference}",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->isJson()->getRequest();
    }
}
