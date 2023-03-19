<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class LoadSelect
 * @package DHL\Dhl24pl\Block\System\Config
 */
class LoadSelect extends Field
{

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
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
