<?php

declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\ShipmentReturn;


use Magento\Framework\Data\OptionSourceInterface;

class LabelType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'BLP', 'label' => __('Etykieta BLP w formacie PDF')],
            ['value' => 'LBLP', 'label' => __('Etykieta BLP w formacie PDF A4')],
            ['value' => 'ZBLP', 'label' => __('Etykieta BLP w formacie drukarek Zebra')],
        ];
    }
}
