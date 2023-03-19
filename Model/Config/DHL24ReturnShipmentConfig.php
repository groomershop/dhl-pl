<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class DHL24ReturnShipmentConfig
 * @package DHL\Dhl24pl\Model\Config
 */
class DHL24ReturnShipmentConfig
{
    const BILLING_ACCOUNT_NUMBER = "dhl24pl/return/billing_account_number";

    const LABEL_EXP_DAYS = "dhl24pl/return/label_exp_days";

    const USE_PRIMARY_SENDER_AS_RECEIVER = "dhl24pl/return/use_primary_sender_as_receiver";
    const RECEIVER_POST_NUMBER = "dhl24pl/return/receiver_post_number";
    const RECEIVER_NAME = "dhl24pl/return/receiver_name";
    const RECEIVER_POSTCODE = "dhl24pl/return/receiver_postcode";
    const RECEIVER_CITY = "dhl24pl/return/receiver_city";
    const RECEIVER_STREET = "dhl24pl/return/receiver_street";
    const RECEIVER_HOUSE_NUMBER = "dhl24pl/return/receiver_house_number";
    const RECEIVER_APARTMENT_NUMBER = "dhl24pl/return/receiver_apartment_number";
    const RECEIVER_COUNTRY = "dhl24pl/return/receiver_country";

    const PREAVISO_PERSON_NAME = "dhl24pl/return/shipper_preaviso_person_name";
    const PREAVISO_PHONE_NUMBER = "dhl24pl/return/preaviso_phone_number";
    const PREAVISO_EMAIL_ADDRESS = "dhl24pl/return/preaviso_email_address";

    const CONTACT_PERSON_NAME = "dhl24pl/return/contact_person_name";
    const CONTACT_PHONE_NUMBER = "dhl24pl/return/contact_phone_number";
    const CONTACT_EMAIL_ADDRESS = "dhl24pl/return/contact_email_address";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * DHL24ReturnShipmentConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string|null
     */
    public function getBillingAccountNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::BILLING_ACCOUNT_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getLabelExpDays(): ?string
    {
        return $this->scopeConfig->getValue(self::LABEL_EXP_DAYS);
    }

    /**
     * @return bool
     */
    public function shouldUsePrimarySenderAsReturnReceiver(): bool
    {
        return $this->scopeConfig->getValue(self::USE_PRIMARY_SENDER_AS_RECEIVER) == '1';
    }

    /**
     * @return string|null
     */
    public function getReceiverPostNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_POST_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getReceiverName(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_NAME);
    }

    /**
     * @return string|null
     */
    public function getReceiverPostCode(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_POSTCODE);
    }

    /**
     * @return string|null
     */
    public function getReceiverCity(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_CITY);
    }

    /**
     * @return string|null
     */
    public function getReceiverStreet(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_STREET);
    }

    /**
     * @return string|null
     */
    public function getReceiverHouseNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_HOUSE_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getReceiverApartmentNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_APARTMENT_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getReceiverCountry(): ?string
    {
        return $this->scopeConfig->getValue(self::RECEIVER_COUNTRY);
    }

    /**
     * @return string|null
     */
    public function getPreavisoPersonName(): ?string
    {
        return $this->scopeConfig->getValue(self::PREAVISO_PERSON_NAME);
    }

    /**
     * @return string|null
     */
    public function getPreavisoPhoneNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::PREAVISO_PHONE_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getPreavisoEmailAddress(): ?string
    {
        return $this->scopeConfig->getValue(self::PREAVISO_EMAIL_ADDRESS);
    }

    /**
     * @return string|null
     */
    public function getContactPersonName(): ?string
    {
        return $this->scopeConfig->getValue(self::CONTACT_PERSON_NAME);
    }

    /**
     * @return string|null
     */
    public function getContactPhoneNumber(): ?string
    {
        return $this->scopeConfig->getValue(self::CONTACT_PHONE_NUMBER);
    }

    /**
     * @return string|null
     */
    public function getContactEmailAddress(): ?string
    {
        return $this->scopeConfig->getValue(self::CONTACT_EMAIL_ADDRESS);
    }
}
