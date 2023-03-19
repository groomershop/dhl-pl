<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use DHL\Dhl24pl\Helper\Data AS DataHelper;

/**
 * Class Country
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class Country implements OptionSourceInterface
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Country constructor.
     * @param DataHelper $dataHelper
     */
    public function __construct(DataHelper $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Options getter
     *
     * @return array|array[]
     * @throws \Exception
     */
    public function toOptionArray(): array
    {
        $optionArray[] = ['value' => '', 'label' => __('-- wybierz kraj zagraniczny --')];
        foreach ($this->getCountries() as $country) {
            $optionArray[] = ['value' =>  $country->countryCode, 'label' =>  $country->countryName];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array|array[]
     * @throws \Exception
     */
    public function toArray(): array
    {
        $optionArray[] = ['' => __('-- wybierz kraj zagraniczny --')];

        foreach ($this->getCountries() as $country) {
            $optionArray[] = [$country->countryCode =>  $country->countryName];
        }

        return $optionArray;
    }

    /**
     * @return array
     * @throws \SoapFault
     */
    protected function getCountries(): array
    {
        return $this->dataHelper->getCountries();
    }
}
