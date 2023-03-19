<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class ChangeSelect
 * @package DHL\Dhl24pl\Block\System\Config
 */
class ChangeSelect extends Field
{

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $html = parent::render($element);
        $html .= '<script type="text/javascript">
        document.getElementById("dhl24pl_cod_type").addEventListener("change", function () {
            var e = document.getElementById("dhl24pl_cod_type");
            var value = e.options[e.selectedIndex].value;
            if (value == "cashondelivery") {
                document.getElementById("dhl24pl_cod_variant").disabled = false;
            } else {
                document.getElementById("dhl24pl_cod_variant").disabled = true;
                document.getElementById("dhl24pl_cod_variant").value = "";
            }

        });

        </script>';

        return $html;
    }
}
