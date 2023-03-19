<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use DHL\Dhl24pl\Helper\CarrierCountryPrice;

/**
 * Class Country
 * @package DHL\Dhl24pl\Block\Adminhtml\Form\Field
 */
class Country extends Select
{
    /**
     * @var CarrierCountryPrice
     */
    private $carrierCountryPriceHelper;

    /**
     * Country constructor.
     * @param Context $context
     * @param CarrierCountryPrice $carrierCountryPriceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CarrierCountryPrice $carrierCountryPriceHelper,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->carrierCountryPriceHelper = $carrierCountryPriceHelper;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->carrierCountryPriceHelper->getCountries());
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
