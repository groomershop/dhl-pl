<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;

class Recipient extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DHL_Dhl24pl::dhlpl');
    }

    public function execute() {
        $oid = $this->getRequest()->getParam('oid');
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $recipient = $shipmentHelper->getFromOrderData($oid);
        $jsonData = json_encode($recipient);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);

    }

}
