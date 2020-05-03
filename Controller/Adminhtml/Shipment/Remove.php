<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;


class Remove extends \Magento\Backend\App\Action
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
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');
        $oid = $this->getRequest()->getParam('order_id');
        $order = $shipmentHelper->getOrder($oid);
        if (!$shipmentHelper->isOrder($order)) {
            $this->messageManager->addError('Nie udało się znaleść zamówienia');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        $settings = $order->getDhlplSettings();
        if (empty($settings)) {
            $this->messageManager->addError('Do zamówienia nie jest przypisana przesyłka');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        $settings = json_decode($settings);

        if (!isset($settings->lp) || empty($settings->lp)) {
            $this->messageManager->addError('Zamówienie nie posiada przypisanego nuemru zamówienia');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        if (isset($settings->type) && $settings->type == 'SERVICEPOINT') {//service pont
            if (!$shipmentHelper->isLm()) {
                $this->messageManager->addError('Nie można usunąć przesyłki. Brak danych dostępowych do api servicepoint');
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
            try{
                $result = $apiHelper->removeServicePointShipment($settings->lp);
                if ($result->deleteShipmentResult->status == 'OK') {
                    $shipmentHelper->saveShipmentParams($oid, null);
                    $this->messageManager->addSuccess('Przesyłka została usunięta.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                } else {
                    $this->messageManager->addError('Nie można usunąć przesyłki.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
        } else {//webapi
            try {
                $result = $apiHelper->removeWebapiShipment($settings->lp);
                if (!$result->deleteShipmentResult->result) {//nie udalo sie usunac przesylki
                    $this->messageManager->addError('Nie można usunąć przesyłki. ' . $result->deleteShipmentResult->error);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                } else {
                    $shipmentHelper->saveShipmentParams($oid, '');
                    $this->messageManager->addSuccess('Przesyłka została usunięta.');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('sales/order/index');
                return $resultRedirect;
            }
        }
    }

}
