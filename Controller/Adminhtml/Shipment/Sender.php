<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;

class Sender extends \Magento\Backend\App\Action
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
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');
        $magentoData = $apiHelper->getMagentoFromDHL();
        $result = array();
        if ($magentoData['error'] === false) {
            if (isset($magentoData['data']['senders'][$name])) {
                $result = $magentoData['data']['senders'][$name];
            }
        }
        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}
