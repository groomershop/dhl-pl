<?php
namespace DHL\Dhl24pl\Block\Adminhtml\Shipment;

class Lmmap extends \Magento\Backend\Block\Template
{

    protected $_objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function getLmmapUrl() {
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');
        return $apiHelper->getLmmap();

    }




}
