<?php

namespace Paygreen\Sdk\Charity\V2\Request;

use Paygreen\Sdk\Charity\V2\Model\Donation;
use Paygreen\Sdk\Core\Encoder\JsonEncoder;
use Paygreen\Sdk\Core\Exception\ConstraintViolationException;
use Paygreen\Sdk\Core\Normalizer\CleanEmptyValueNormalizer;
use Paygreen\Sdk\Core\Serializer\Serializer;
use Paygreen\Sdk\Core\Validator\Validator;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DonationRequest extends \Paygreen\Sdk\Core\Request\Request
{
    /**
     * @param Donation $donation
     *
     * @throws ConstraintViolationException
     *
     * @return RequestInterface
     */
    public function getCreateRequest($donation)
    {
        $violations = Validator::validateModel($donation);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        $body = [
            'donationReference' => $donation->getReference(),
            'idAssociation' => $donation->getAssociationId(),
            'type' => $donation->getType(),
            'donationAmount' => $donation->getDonationAmount(),
            'totalAmount' => $donation->getTotalAmount(),
            'currency' => $donation->getCurrency(),
            'buyer' => [
                'email' => $donation->getBuyer()->getEmail(),
                'externalId' => $donation->getBuyer()->getReference(),
                'firstname' => $donation->getBuyer()->getFirstname(),
                'lastname' => $donation->getBuyer()->getLastname(),
                'address' => $donation->getBuyer()->getAddressLine(),
                'address2' => $donation->getBuyer()->getAddressLineTwo(),
                'city' => $donation->getBuyer()->getCity(),
                'zipCode' => $donation->getBuyer()->getPostalCode(),
                'country' => $donation->getBuyer()->getCountryCode(),
                'company' => $donation->getBuyer()->getCompanyName(),
                'phone' => $donation->getBuyer()->getPhoneNumber(),
            ],
            'isAPledge' => $donation->isAPledge()
        ];

        return $this->requestFactory->create(
            "/donation",
            (new Serializer([new CleanEmptyValueNormalizer()], [new JsonEncoder()]))->serialize($body, 'json')
        )->withAuthorization()->withTestMode()->isJson()->getRequest();
    }

    /**
     * @param integer $donationId
     *
     * @throws ConstraintViolationException
     *
     * @return RequestInterface
     */
    public function getGetRequest($donationId)
    {
        $violations = Validator::validateValue($donationId, [
            new Assert\NotBlank(),
            new Assert\Type('integer'),
        ]);

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, 'Request parameters validation has failed.');
        }

        return $this->requestFactory->create(
            "/donation/{$donationId}",
            null,
            'GET'
        )->withAuthorization()->withTestMode()->getRequest();
    }
}