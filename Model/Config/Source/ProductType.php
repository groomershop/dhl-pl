<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ProductType
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class ProductType implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  'AH', 'label' =>  __('DHL PARCEL POLSKA')],
            ['value' =>  '09', 'label' =>  __('DHL PARCEL 9')],
            ['value' =>  '12', 'label' =>  __('DHL PARCEL 12')],
            ['value' =>  'AHE', 'label' =>  __('Doręczenie w godzinach 18-22')],
            ['value' =>  'PR', 'label' =>  __('DHL PARCEL PREMIUM')],
            ['value' =>  'AHM', 'label' =>  __('DHL PARCEL MAX')],
            ['value' =>  'EK', 'label' =>  __('DHL PARCEL CONNECT')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'AH' =>  __('DHL PARCEL POLSKA'),
            '09' =>  __('DHL PARCEL 9'),
            '12' =>  __('DHL PARCEL 12'),
            'AHE' =>  __('Doręczenie w godzinach 18-22'),
            'PR' =>  __('DHL PARCEL PREMIUM'),
            'AHM' =>  __('DHL PARCEL MAX'),
            'EK' =>  __('DHL PARCEL CONNECT')
        ];
    }
}
