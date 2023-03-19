<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Cod;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 * @package DHL\Dhl24pl\Model\Config\Cod
 */
class Type implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'cashondelivery', 'label' => __('COD wbudowane w Magento')],
            ['value' => 'empty', 'label' => __('Brak COD')]
        ];
    }
}
