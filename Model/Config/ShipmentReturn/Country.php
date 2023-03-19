<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\ShipmentReturn;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Country
 * @package DHL\Dhl24pl\Model\Config\ShipmentReturn
 */
class Country implements OptionSourceInterface
{
    /**
     * @return array|array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'PL', 'label' => __('Poland')],
        ];
    }
}
