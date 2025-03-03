<?php

namespace Paygreen\Sdk\Payment\V2\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Order implements OrderInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var AddressInterface
     */
    private $shippingAddress;

    /**
     * @var AddressInterface
     */
    private $billingAddress;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('reference', new Assert\NotBlank())
            ->addPropertyConstraints('customer', [
                new Assert\NotBlank(),
                new Assert\Type(CustomerInterface::class),
                new Assert\Valid(),
            ])
            ->addPropertyConstraints('shippingAddress', [
                new Assert\NotBlank(),
                new Assert\Type(AddressInterface::class),
                new Assert\Valid(),
            ])
            ->addPropertyConstraints('billingAddress', [
                new Assert\NotBlank(),
                new Assert\Type(AddressInterface::class),
                new Assert\Valid(),
            ])
            ->addPropertyConstraints('amount', [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
            ])
            ->addPropertyConstraints('currency', [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ])
        ;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return AddressInterface
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param AddressInterface $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return AddressInterface
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param AddressInterface $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
