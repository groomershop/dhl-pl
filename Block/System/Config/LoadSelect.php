<?php

namespace DHL\Dhl24pl\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class LoadSelect extends \Magento\Config\Block\System\Config\Form\Field
{

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::render($element);
        $html .= '<script type="text/javascript">
        var typeE = document.getElementById("dhl24pl_cod_type");
        var typeValue = typeE.options[typeE.selectedIndex].value;
        if (typeValue == "cashondelivery") {
            document.getElementById("dhl24pl_cod_variant").disabled = false;
        } else {
            document.getElementById("dhl24pl_cod_variant").disabled = true;
            document.getElementById("dhl24pl_cod_variant").value = "";
        }
</script>';


        return $html;
    }


}