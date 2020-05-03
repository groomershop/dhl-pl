<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;


class Shipment extends \Magento\Backend\App\Action
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
        $name = $this->getRequest()->getParam('name');
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');
        $magentoData = $apiHelper->getMagentoFromDHL();
        $result = array();
        if ($magentoData['error'] === false) {
            $magentoData = $magentoData['data'];
            if (isset($magentoData['shipments'][$name])) {
                $result['shipment'] = $magentoData['shipments'][$name];
                $senderName = $magentoData['shipments'][$name]['senderName'];
                if (isset($magentoData['senders'][$senderName])) {
                    $result['sender'] = $magentoData['senders'][$senderName];
                }
            }
            if ($shipmentHelper->canCourier($magentoData, $name)) {
                $result['shipment']['courier'] = true;
            } else {
                $result['shipment']['courier'] = false;
            }
        }
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}
