<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Cod;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Variant
 * @package DHL\Dhl24pl\Model\Config\Cod
 */
class Variant implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'all', 'label' => __('Suma za towary z zamówienia i koszt transportu ')],
            ['value' => 'products', 'label' => __('Tylko suma za towary z zamówienia')]
        ];
    }
}
