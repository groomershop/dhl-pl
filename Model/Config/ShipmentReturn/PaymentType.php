<?php

declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\ShipmentReturn;


use Magento\Framework\Data\OptionSourceInterface;

class PaymentType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'RECEIVER', 'label' => __('Odbiorca')],
        ];
    }
}
