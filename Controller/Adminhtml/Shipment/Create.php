<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
    Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory
) {
    $this->resultPageFactory = $resultPageFactory;
    parent::__construct($context);
}

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
{
    return $this->_authorization->isAllowed('DHL_Dhl24pl::dhlpl');
}

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
{

    $resultPage = $this->resultPageFactory->create();
    $resultPage->setActiveMenu('Magento_Sales::sales_order');
    $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
    $resultPage->getConfig()->getTitle()->set('DHL24');
    return $resultPage;
}

    public function execute() {
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');
        $oid = $this->getRequest()->getParam('order_id');
        $order = $shipmentHelper->getOrder($oid);
        if (!$shipmentHelper->isOrder($order)) {
            $this->messageManager->addError('Niepoprawne zamówienie');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        if ($shipmentHelper->isAlreadyCreated($order)) {
            $this->messageManager->addError('Dla wybranego zamówienia nie można utworzyć przesyłki');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
        $magentoData = $apiHelper->getMagentoFromDHL();
        if ($magentoData['error'] !== false) {
            $this->messageManager->addError($magentoData['error']);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }

        $magentoData = $magentoData['data'];
        $allShipment = $shipmentHelper->prepareAllShipment($magentoData, $order);

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $allShipment = $shipmentHelper->prepareAllShipmentAfterPost($magentoData, $data, $_POST);
            $magentoSender = $shipmentHelper->prepareMagentoSender($magentoData);
            if ($shipmentHelper->validate($allShipment)) {
                try {
                    if ($shipmentHelper->isLm() && $shipmentHelper->isLmShipment($allShipment)) {
                        $result = $apiHelper->createServicePointShipment($allShipment, $magentoData, $magentoSender);
                        $params = array(
                            'lp' => $result->createShipmentResult->shipmentNumber,
                            'labeltype' => $result->createShipmentResult->label->labelType,
                            'type' => 'SERVICEPOINT'
                        );
                        $shipmentHelper->saveShipmentParams($oid, json_encode($params));

                        $this->getResponse ()
                            ->setHeader ( 'Content-type', $result->createShipmentResult->label->labelFormat )
                            ->setHeader ('Content-Disposition', 'attachment' . '; filename=' . $result->createShipmentResult->label->labelName );
                        $this->getResponse ()->setBody(base64_decode($result->createShipmentResult->label->labelContent));
                    } else {
                        $result = $apiHelper->createWebapiShipment($allShipment, $magentoData, $magentoSender);
                        $params = array(
                            'lp' => $result->createShipmentResult->shipmentNotificationNumber,
                            'labeltype' => $result->createShipmentResult->label->labelType,
                            'type' => 'WEBAPI'
                        );
                        $shipmentHelper->saveShipmentParams($oid, json_encode($params));
                        $name = $result->createShipmentResult->shipmentNotificationNumber . '.pdf';
                        if ($result->createShipmentResult->label->labelType == 'ZBLP') {
                            $name = $result->createShipmentResult->shipmentNotificationNumber . '.zpl';
                        }

                        $this->getResponse ()
                            ->setHeader ( 'Content-type', $result->createShipmentResult->label->labelFormat )
                            ->setHeader ('Content-Disposition', 'attachment' . '; filename=' . $name );
                        $this->getResponse ()->setBody(base64_decode($result->createShipmentResult->label->labelContent));
                    }
                } catch (\Exception $e) {
                    $shipmentHelper->setError($e->getMessage());
                }
            }
        }
        if ($shipmentHelper->isError()) {
            $this->messageManager->addError($shipmentHelper->getError());
        }

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $order->getIncrementId()));
        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_create')->setShipmentValues($allShipment);
        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_create')->setMagentoData($magentoData);
        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_create')->setOrderId($oid);
        return $resultPage;
    }
}
