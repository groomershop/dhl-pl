<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use DHL\Dhl24pl\Helper\Api AS ApiHelper;
use DHL\Dhl24pl\Helper\Shipment AS ShipmentHelper;

/**
 * Class Remove
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Remove extends \Magento\Backend\App\Action
{
    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ShipmentHelper
     */
    protected $shipmentHelper;

    /**
     * Remove constructor.
     * @param Action\Context $context
     * @param ApiHelper $apiHelper
     * @param ShipmentHelper $shipmentHelper
     */
    public function __construct(
        Action\Context $context,
        ApiHelper $apiHelper,
        ShipmentHelper $shipmentHelper
    ) {
        $this->apiHelper = $apiHelper;
        $this->shipmentHelper = $shipmentHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        $oid = $this->getRequest()->getParam('order_id');
        $order = $this->shipmentHelper->getOrder($oid);
        if (!$this->shipmentHelper->isOrder($order)) {
            $this->messageManager->addErrorMessage('Nie udało się znaleść zamówienia');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        $settings = $order->getDhlplSettings();
        if (empty($settings)) {
            $this->messageManager->addErrorMessage('Do zamówienia nie jest przypisana przesyłka');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        $settings = json_decode($settings);

        if (!isset($settings->lp) || empty($settings->lp)) {
            $this->messageManager->addErrorMessage('Zamówienie nie posiada przypisanego nuemru zamówienia');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        if (isset($settings->type) && $settings->type == 'SERVICEPOINT') {//service pont
            if (!$this->shipmentHelper->isLm()) {
                $this->messageManager->addErrorMessage('Nie można usunąć przesyłki. Brak danych dostępowych do api servicepoint');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
            try{
                $result = $this->apiHelper->removeServicePointShipment($settings->lp);
                if ($result->deleteShipmentResult->status == 'OK') {
                    $this->shipmentHelper->saveShipmentParams($oid, "{}");
                    $this->messageManager->addSuccessMessage('Przesyłka została usunięta.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                } else {
                    $this->messageManager->addErrorMessage('Nie można usunąć przesyłki.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
        } else {
            try {
                $result = $this->apiHelper->removeWebapiShipment($settings->lp, $settings->dispatchNotificationNumber);
                if (!$result->deleteShipmentResult->result) {//nie udalo sie usunac przesylki
                    $this->messageManager->addErrorMessage('Nie można usunąć przesyłki. ' . $result->deleteShipmentResult->error);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                } else {
                    $this->shipmentHelper->saveShipmentParams($oid, "{}");
                    $this->messageManager->addSuccessMessage('Przesyłka została usunięta.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
        }
    }
}
