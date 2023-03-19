<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use DHL\Dhl24pl\Helper\Api as ApiHelper;
use DHL\Dhl24pl\Helper\Shipment as ShipmentHelper;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

/**
 * Class Create
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Create extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ShipmentHelper
     */
    protected $shipmentHelper;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Create constructor.
     * @param Action\Context $context
     * @param RedirectFactory $redirectFactory
     * @param LoggerInterface $logger
     * @param ApiHelper $apiHelper
     * @param ShipmentHelper $shipmentHelper
     * @param PageFactory $resultPageFactory
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Action\Context $context,
        RedirectFactory $redirectFactory,
        LoggerInterface $logger,
        ApiHelper $apiHelper,
        ShipmentHelper $shipmentHelper,
        PageFactory $resultPageFactory,
        OrderRepository $orderRepository
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->apiHelper = $apiHelper;
        $this->shipmentHelper = $shipmentHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $oid = $this->getRequest()->getParam('order_id');
        try {
            $magentoData = $this->apiHelper->getDataForShipment();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage('Unable to get Magento config from DHL API');
            return $resultPage;
        }
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $order = $this->orderRepository->get($oid);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage('Unable to get Order');
                return $resultPage;
            }
            $parcelData = [];
            if($order->getDhlplParcelshop() != NULL) {
                $parcelData = json_decode($order->getDhlplParcelshop(), true);
            }

            $allShipment = $this->shipmentHelper->prepareAllShipmentAfterPost($magentoData, $data);
            $allShipment['parcelData'] = $parcelData;

            if ($this->shipmentHelper->validate($allShipment)) {
                try {
                    $result = $this->apiHelper->createShipment($allShipment, $magentoData);

                    if ($result != null) {
                        $shipmentNumber = $this->apiHelper->shouldUseServicePointApi() ? $result->createShipmentResult->shipmentNumber : $result->createShipmentResult->shipmentNotificationNumber;
                        $returnShipmentNumber = '';

                        if($this->apiHelper->shouldCreateReturn()) {
                            $returnShipmentResult = $this->apiHelper->createReturnShipment($allShipment, $magentoData, $shipmentNumber);
                            if (is_array($returnShipmentResult->createShipmentReturnResult->item)) {
                                $returnShipmentNumber = array_map(function ($item) {
                                    return $item->shipmentNotificationNumber;
                                }, $returnShipmentResult->createShipmentReturnResult->item);
                            } else {
                                $returnShipmentNumber = [$returnShipmentResult->createShipmentReturnResult->item->shipmentNotificationNumber];
                            }
                        }

                        $params = array(
                            'lp' => $shipmentNumber,
                            'returnlp' => $returnShipmentNumber,
                            'labeltype' => $result->createShipmentResult->label->labelType,
                            'type' => $this->apiHelper->shouldUseServicePointApi() ? 'SERVICEPOINT' : 'WEBAPI',
                        );

                        if (property_exists($result->createShipmentResult, 'dispatchNotificationNumber')) {
                            $params['dispatchNotificationNumber'] = $result->createShipmentResult->dispatchNotificationNumber;
                        }

                        $this->shipmentHelper->saveShipmentParams($oid, json_encode($params));

                        $this->messageManager->addSuccessMessage('List przewozowy został poprawnie utworzony!');
                        return $this->resultRedirectFactory->create()->setPath('sales/order/index');
                    }
                } catch (Exception $e) {
                    $this->shipmentHelper->setError($e->getMessage());
                }
            }
        }
        if ($this->shipmentHelper->isError()) {
            $this->messageManager->addErrorMessage($this->shipmentHelper->getError());
        }

        return $resultPage;
    }
}
