<?php

namespace Paygreen\Sdk\Charity\V2\Model;

use Paygreen\Sdk\Charity\V2\Enum\DonationTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Donation
{
    /**
     * @var string
     */
    private $reference;
    
    /**
     * @var integer
     */
    private $associationId;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $donationAmount;

    /**
     * @var integer
     */
    private $totalAmount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var Buyer
     */
    private $buyer;

    /**
     * @var bool
     */
    private $isAPledge;


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraints('reference', [
                new Assert\Type('string'),
            ])
            ->addPropertyConstraints('associationId', [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
            ])
            ->addPropertyConstraints('type', [
                new Assert\NotBlank(),
                new Assert\Choice(DonationTypeEnum::getDonationsTypes()),
                new Assert\Type('string')
            ])
            ->addPropertyConstraints('donationAmount', [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
            ])
            ->addPropertyConstraints('totalAmount', [
                new Assert\NotBlank(),
                new Assert\Type('integer'),
            ])
            ->addPropertyConstraints('currency', [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ])
            ->addPropertyConstraints('buyer', [
                new Assert\NotBlank(),
                new Assert\Type(Buyer::class),
                new Assert\Valid(),
            ])
            ->addPropertyConstraint('isAPledge', new Assert\Type('bool'))

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
     * @return int
     */
    public function getAssociationId()
    {
        return $this->associationId;
    }

    /**
     * @param int $associationId
     */
    public function setAssociationId($associationId)
    {
        $this->associationId = $associationId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param int $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
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

    /**
     * @return Buyer
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param Buyer $buyer
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * @return bool
     */
    public function isAPledge()
    {
        return $this->isAPledge;
    }

    /**
     * @param bool $isAPledge
     */
    public function setIsAPledge($isAPledge)
    {
        $this->isAPledge = $isAPledge;
    }

    /**
     * @return int
     */
    public function getDonationAmount()
    {
        return $this->donationAmount;
    }

    /**
     * @param int $donationAmount
     */
    public function setDonationAmount($donationAmount)
    {
        $this->donationAmount = $donationAmount;
    }
}
