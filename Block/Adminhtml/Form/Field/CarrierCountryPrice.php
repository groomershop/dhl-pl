<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use DHL\Dhl24pl\Block\Adminhtml\Form\Field\Country;
/**
 * Class CarrierCountryPrice
 * @package DHL\Dhl24pl\Block\Adminhtml\Form\Field
 */
class CarrierCountryPrice extends AbstractFieldArray
{
    /**
     * @var Country
     */
    protected $_groupRenderer;

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                Country::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_groupRenderer->setClass('customer_group_select admin__control-select');
        }
        return $this->_groupRenderer;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'country_id',
            ['label' => __('Country'), 'renderer' => $this->_getGroupRenderer()]
        );
        $this->addColumn(
            'country_price',
            [
                'label' => __('Price'),
                'class' => 'required-entry validate-number validate-greater-than-zero admin__control-text'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add price for country');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('country_id'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
