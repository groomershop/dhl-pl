<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DHL\Dhl24pl\Model\Config\Cod;

class Variant implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'all', 'label' => __('Suma za towary z zamówienia i koszt transportu ')],
            ['value' => 'products', 'label' => __('Tylko suma za towary z zamówienia')]
        ];
    }
}
