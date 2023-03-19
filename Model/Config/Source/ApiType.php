<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ApiType
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class ApiType implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  0, 'label' =>  __('Producyjne')],
            ['value' =>  1, 'label' =>  __('Testowe')]
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
            0 => __('Producyjne'),
            1 => __('Testowe')
        ];
    }
}
