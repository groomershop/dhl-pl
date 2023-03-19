<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PayerType
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class PayerType implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  'N', 'label' =>  __('Nadawca')],
            ['value' =>  'T', 'label' =>  __('Zleceniodawca')]
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
            'N' => __('Nadawca'),
            'T' => __('Zleceniodawca')
        ];
    }
}
