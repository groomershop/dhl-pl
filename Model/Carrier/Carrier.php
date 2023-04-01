<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use DHL\Dhl24pl\Helper\CarrierCountryPrice;
use Magento\Checkout\Model\Session;
use DHL\Dhl24pl\Helper\Api as ApiHelper;

/**
 * Class Carrier
 * @package DHL\Dhl24pl\Model\Carrier
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    const CARRIER_CODE = 'dhl_dhl24pl';
    const CARRIER_COURIER_CODE = 'courier';
    const CARRIER_COURIER_COD = 'cod';
    const CARRIER_PARCELSHOP_CODE = 'parcelshop';
    const CARRIER_PARCELSHOP_COD_CODE = 'parcelshop_cod';
    const CARRIER_COURIER_EVENING = 'evening';
    const CARRIER_COURIER_DHL12 = 'dhl12';
    const CARRIER_COURIER_DHL09 = 'dhl09';
    const CARRIER_COURIER_DHL_PREMIUM = 'dhlpremium';
    const CARRIER_COURIER_DHL_MAX = 'dhlmax';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var CarrierCountryPrice
     */
    protected $carrierCountryPriceHelper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * Carrier constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CarrierCountryPrice $carrierCountryPriceHelper
     * @param Session $checkoutSession
     * @param ApiHelper $apiHelper
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CarrierCountryPrice $carrierCountryPriceHelper,
        Session $checkoutSession,
        ApiHelper $apiHelper,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->carrierCountryPriceHelper = $carrierCountryPriceHelper;
        $this->checkoutSession = $checkoutSession;
        $this->apiHelper = $apiHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array|string[]
     */
    public function getAllowedMethods()
    {
        return array(
            self::CARRIER_COURIER_CODE => $this->getConfigData('title_courier'),
            self::CARRIER_COURIER_COD => $this->getConfigData('title_cod'),
            self::CARRIER_PARCELSHOP_CODE => $this->getConfigData('title_parcelshop'),
            self::CARRIER_PARCELSHOP_COD_CODE => $this->getConfigData('title_parcelshop_cod'),
            self::CARRIER_COURIER_EVENING => $this->getConfigData('title_evening'),
            self::CARRIER_COURIER_DHL12 => $this->getConfigData('title_dhl12'),
            self::CARRIER_COURIER_DHL09=> $this->getConfigData('title_dhl09'),
            self::CARRIER_COURIER_DHL_PREMIUM=> $this->getConfigData('title_premium'),
            self::CARRIER_COURIER_DHL_MAX=> $this->getConfigData('title_max'),
        );
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->rateResultFactory->create();

        $result->append($this->getCourierShippingRate($request));

        if($request->getDestCountryId() == 'PL') {
            if ($this->getConfigFlag('active_cod')) {
                $result->append($this->getCashOnDelivery());
            }
        }

        $result->append($this->getParcelshopShippingMethod($request));

        if($request->getDestCountryId() == 'PL') {
            if ($this->getConfigFlag('active_parcelshop_cod')) {
                $result->append($this->getParcelshopCashOnDelivery());
            }

            if(!empty($request->getDestPostcode())) {
                if ($this->getConfigFlag('active_evening') && $this->getPostalCodeServicesByPostCode($request->getDestPostcode())->deliveryEvening) {
                    $result->append($this->getEvening());
                }

                if ($this->getConfigFlag('active_dhl12') && $this->getPostalCodeServicesByPostCode($request->getDestPostcode())->domesticExpress12) {
                    $result->append($this->getDhl12());
                }

                if ($this->getConfigFlag('active_dhl09') && $this->getPostalCodeServicesByPostCode($request->getDestPostcode())->domesticExpress9) {
                    $result->append($this->getDhl09());
                }
            }

            if ($this->getConfigFlag('active_premium')) {
                $result->append($this->getDhlPremium());
            }

            if ($this->getConfigFlag('active_max')) {
                $result->append($this->getDhlMax());
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    protected function getCourierShippingRate(RateRequest $request)
    {
        $countriesArray = $this->carrierCountryPriceHelper->getCountriesArray();
        $countryId = $request->getDestCountryId();

        if(isset($countriesArray[$countryId]) && $countriesArray[$countryId]) {
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod(self::CARRIER_COURIER_CODE);
            $method->setMethodTitle($this->getConfigData('title_courier'));

            $method->setPrice($countriesArray[$countryId]);
            $method->setCost(0);
            return $method;
        }
    }

    /**
     * @return mixed
     */
    protected function getCashOnDelivery()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_COD);
        $method->setMethodTitle($this->getConfigData('title_cod'));
        $price = $this->getConfigData('price_cod');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getParcelshopShippingMethod(RateRequest $request)
    {
        $countriesArray = $this->carrierCountryPriceHelper->getCountriesParcelshopArray();
        $countryId = $request->getDestCountryId();

        if(isset($countriesArray[$countryId]) && $countriesArray[$countryId]) {
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);

            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod(self::CARRIER_PARCELSHOP_CODE);
            $method->setMethodTitle($this->getConfigData('title_parcelshop'));


            $method->setPrice($countriesArray[$countryId]);
            $method->setCost(0);

            return $method;
        }
    }

    /**
     * @return mixed
     */
    protected function getParcelshopCashOnDelivery()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_PARCELSHOP_COD_CODE);
        $method->setMethodTitle($this->getConfigData('title_parcelshop_cod'));
        $price = $this->getConfigData('price_parcelshop_cod');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getEvening()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_EVENING);
        $method->setMethodTitle($this->getConfigData('title_evening'));
        $price = $this->getConfigData('price_evening');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getDhl12()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_DHL12);
        $method->setMethodTitle($this->getConfigData('title_dhl12'));
        $price = $this->getConfigData('price_dhl12');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getDhl09()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_DHL09);
        $method->setMethodTitle($this->getConfigData('title_dhl09'));
        $price = $this->getConfigData('price_dhl09');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getDhlPremium()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_DHL_PREMIUM);
        $method->setMethodTitle($this->getConfigData('title_premium'));
        $price = $this->getConfigData('price_premium');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @return mixed
     */
    protected function getDhlMax()
    {
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::CARRIER_COURIER_DHL_MAX);
        $method->setMethodTitle($this->getConfigData('title_max'));
        $price = $this->getConfigData('price_max');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }

    /**
     * @param $postCode
     * @return mixed
     * @throws \SoapFault
     */
    protected function getPostalCodeServicesByPostCode($postCode)
    {
        return $this->apiHelper->getPostalCodeServicesByPostCode($postCode)->getPostalCodeServicesResult;
    }
}
