<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DHL\Dhl24pl\Model\Config\Cod;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'cashondelivery', 'label' => __('COD wbudowane w Magento')],
            ['value' => 'empty', 'label' => __('Brak COD')]
        ];
    }
}
