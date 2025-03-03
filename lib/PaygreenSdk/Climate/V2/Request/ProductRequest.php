<?php

namespace Paygreen\Sdk\Climate\V2\Request;

use Exception;
use GuzzleHttp\Psr7\MultipartStream;
use Paygreen\Sdk\Climate\V2\Model\CartItem;
use Paygreen\Sdk\Core\Encoder\JsonEncoder;
use Paygreen\Sdk\Core\Exception\ConstraintViolationException;
use Paygreen\Sdk\Core\Normalizer\CleanEmptyValueNormalizer;
use Paygreen\Sdk\Core\Serializer\Serializer;
use Paygreen\Sdk\Core\Validator\Validator;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductRequest extends \Paygreen\Sdk\Core\Request\Request
{
    /**
     * @param string $footprintId
     * @param string $productExternalReference
     * @param integer $quantity
     * @return RequestInterface
     * @throws ConstraintViolationException
     */
    public function getAddProductDataRequest(
        $footprintId,
        $productExternalReference,
        $quantity
    ) {
        $violations = Validator::validateValue($footprintId, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length([
                'min' => 0,
                'max' => 100,
            ]),
            new Assert\Regex([
                'pattern' => '/^[a-zA-Z0-9_-]{0,100}$/'
            ])
        ]);
        $violations->addAll(Validator::validateValue($productExternalReference, [
            new Assert\NotBlank(),
            new Assert\Type('string')
        ]));
        $violations->addAll(Validator::validateValue($quantity, [
            new Assert\NotBlank(),
            new Assert\Type('integer')
        ]));

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $body = [
            'productExternalReference' => $productExternalReference,
            'quantity' => $quantity
        ];

        return $this->requestFactory->create(
            "/carbon/footprints/{$footprintId}/products",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->withTestMode()->isJson()->getRequest();
    }

    /**
     * @param string $footprintId
     * @param CartItem[] $cartItems
     *
     * @throws ConstraintViolationException
     * @throws Exception
     * @return RequestInterface
     */
    public function getAddProductsDataRequest($footprintId, $cartItems)
    {
        $violations = Validator::validateValue($footprintId, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length([
                'min' => 0,
                'max' => 100,
            ]),
            new Assert\Regex([
                'pattern' => '/^[a-zA-Z0-9_-]{0,100}$/'
            ])
        ]);

        foreach ($cartItems as $cartItem) {
            $violations->addAll(Validator::validateModel($cartItem));
        }

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }
        
        $products = array();

        foreach ($cartItems as $cartItem) {
            $products[] = array(
                'productExternalReference' => $cartItem->getProductReference(),
                'quantity' => $cartItem->getQuantity(),
                'exTaxPriceInCents' => $cartItem->getPriceWithoutTaxes()
            );
        }

        $body = ['products' => $products];

        return $this->requestFactory->create(
            "/carbon/footprints/{$footprintId}/product-cart",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->withTestMode()->isJson()->getRequest();
    }

    /**
     * @param string $productExternalReference
     * @param string $productName
     * @param null|string $emissionExternalId
     *
     * @throws ConstraintViolationException
     *
     * @return RequestInterface
     */
    public function getCreateProductReferenceRequest(
        $productExternalReference,
        $productName,
        $emissionExternalId = null
    ) {
        $violations = Validator::validateValue($productExternalReference, [
            new Assert\NotBlank(),
            new Assert\Type('string')
        ]);
        $violations->addAll(Validator::validateValue($productName, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ]));
        $violations->addAll(Validator::validateValue($emissionExternalId, [
            new Assert\Type('string'),
        ]));

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $body = [
            'productExternalReference' => $productExternalReference,
            'productName' => $productName,
            'emissionExternalId' => $emissionExternalId,
        ];

        return $this->requestFactory->create(
            "/carbon/products/references",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->withTestMode()->isJson()->getRequest();
    }

    /**
     * @param string $footprintId
     * @param null|string $productExternalReference
     *
     * @return RequestInterface
     * @throws Exception
     *
     * @throws ConstraintViolationException
     */
    public function getDeleteProductDataRequest($footprintId, $productExternalReference = null)
    {
        $violations = Validator::validateValue($footprintId, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length([
                'min' => 0,
                'max' => 100,
            ]),
        ]);
        $violations->addAll(Validator::validateValue($productExternalReference, [
            new Assert\Type('string')
        ]));

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        if (!empty($productExternalReference)) {
            $url = "/carbon/footprints/{$footprintId}/products/$productExternalReference";
        } else {
            $url = "/carbon/footprints/{$footprintId}/products";
        }

        return $this->requestFactory->create(
            $url,
            null,
            'DELETE'
        )->withAuthorization()->withTestMode()->getRequest();
    }

    /**
     * @param string $filepath
     *
     * @return RequestInterface
     * @throws Exception
     *
     * @throws ConstraintViolationException
     */
    public function getExportProductCatalogRequest($filepath)
    {
        $violations = Validator::validateValue($filepath, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ]);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $multipart = new MultipartStream([
            [
                'name' => 'inputCsv',
                'contents' => fopen($filepath, 'r'),
                'filename' => 'product_catalog.csv'
            ]
        ]);

        return $this->requestFactory->create(
            '/carbon/products/catalog',
            $multipart,
            'POST',
            [
                'Accept' => '*/*',
                'Content-Type' => 'multipart/form-data; boundary=' . $multipart->getBoundary(),
                'Accept-Encoding' => 'gzip, deflate, br',
                'Cache-Control' => 'no-cache'
            ]
        )->withAuthorization()->withTestMode()->getRequest();
    }
}