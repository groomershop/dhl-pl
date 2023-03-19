<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Administration;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PrintLabel
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class PrintLabel implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  null, 'label' =>  __('-- wybierz --')],
            ['value' =>  'BLP', 'label' =>  __('BLP')],
            ['value' =>  'ZBLP', 'label' =>  __('ZBLP')],
            ['value' =>  'LP', 'label' =>  __('LP')],
            ['value' =>  'LBLP', 'label' =>  __('LBLP')]
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
            null => __('-- wybierz --'),
            'BLP' => __('BLP'),
            'ZBLP' => __('ZBLP'),
            'LP' => __('LP'),
            'LBLP' => __('LBLP')
        ];
    }
}
