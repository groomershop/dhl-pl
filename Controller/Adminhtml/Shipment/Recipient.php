<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use DHL\Dhl24pl\Helper\Shipment as ShipmentHelper;

/**
 * Class Recipient
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Recipient extends Action
{
    /**
     * @var ShipmentHelper
     */
    protected $shipmentHelper;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        ShipmentHelper $shipmentHelper
    ) {
        $this->shipmentHelper = $shipmentHelper;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('DHL_Dhl24pl::dhlpl');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute() {
        $oid = $this->getRequest()->getParam('oid');
        $recipient = $this->shipmentHelper->getFromOrderData($oid);
        $jsonData = json_encode($recipient);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);

    }

}
