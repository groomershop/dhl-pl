<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use DHL\Dhl24pl\Model\Config\Source\Countries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CarrierCountryPrice
 * @package DHL\Dhl24pl\Helper
 */
class CarrierCountryPrice extends AbstractHelper
{
    const COUNTRY_COURIER_PRICE_PATH = 'carriers/dhl_dhl24pl/price_courier';
    const COUNTRY_COURIER_PARCELSHOP_PRICE_PATH = 'carriers/dhl_dhl24pl/price_parcelshop';

    /**
     * @var array
     */
    private $country = [];

    /**
     * @var Countries
     */
    private $allCountries;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CarrierCountryPrice constructor.
     * @param Countries $allCountries
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Countries $allCountries,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->allCountries = $allCountries;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array|array[]
     * @throws \Exception
     */
    public function getCountries(): array
    {
        if (!$this->country) {
            $this->country = $this->allCountries->toOptionArray();
        }
        return $this->country;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountriesArray(): array
    {
        $countryPrice = unserialize($this->scopeConfig->getValue(
            self::COUNTRY_COURIER_PRICE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId())
        );

        return is_array($countryPrice) ? $countryPrice : [];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountriesParcelshopArray(): array
    {
        $countryPrice = unserialize($this->scopeConfig->getValue(
            self::COUNTRY_COURIER_PARCELSHOP_PRICE_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId())
        );

        return is_array($countryPrice) ? $countryPrice : [];
    }
}
