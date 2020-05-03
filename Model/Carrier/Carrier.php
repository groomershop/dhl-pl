<?php
namespace DHL\Dhl24pl\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'dhl_dhl24pl';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'parcelshop' => 'DHL Parcelshop',
            'courier' => 'DHL Kurier',
        );
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();

        $result->append($this->_getParcelshopShippingMethod());
        $result->append($this->_getCourierShippingRate());

        return $result;
    }

    protected function _getParcelshopShippingMethod()
    {
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);

        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('parcelshop');
        $method->setMethodTitle('DHL Parcelshop');

        $price = $this->getConfigData('price_parcelshop');
        $method->setPrice($price);
        $method->setCost(0);

        return $method;
    }

    protected function _getCourierShippingRate()
    {
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('courier');
        $method->setMethodTitle('DHL Kurier');
        $price = $this->getConfigData('price_courier');
        $method->setPrice($price);
        $method->setCost(0);
        return $method;
    }
}