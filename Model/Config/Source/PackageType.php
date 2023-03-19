<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PackageType
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class PackageType implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  'ENVELOPE', 'label' =>  __('koperta')],
            ['value' =>  'PACKAGE', 'label' =>  __('paczka')],
            ['value' =>  'PALLET', 'label' =>  __('paleta')]
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
            'ENVELOPE' => __('koperta'),
            'PACKAGE' => __('paczka'),
            'PALLET' => __('paleta')
        ];
    }
}
