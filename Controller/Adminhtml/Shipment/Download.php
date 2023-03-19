<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use DHL\Dhl24pl\Helper\Api;
use DHL\Dhl24pl\Helper\Api as ApiHelper;
use DHL\Dhl24pl\InvalidShipmentTypeException;
use DHL\Dhl24pl\Model\Shipment\ShipmentType;
use DHL\Dhl24pl\Ui\Component\Listing\Column\Dhl;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;
use SoapFault;
use ZipArchive;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Download
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Download extends Action
{
    const PRINT_LABEL_CONFIG_PATH = 'dhl24pl/administration/print_label';
    const DEFAULT_PRINT_LABEL = 'BLP';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Download constructor.
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param ApiHelper $apiHelper
     * @param PageFactory $resultPageFactory
     * @param OrderRepository $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        ApiHelper $apiHelper,
        PageFactory $resultPageFactory,
        OrderRepository $orderRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->apiHelper = $apiHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $oid = $this->getRequest()->getParam('order_id');
        $shipmentType = $this->getRequest()->getParam(Dhl::SHIPMENT_TYPE_PARAM_NAME);
        try {
            $order = $this->orderRepository->get($oid);
            $shipmentSettingsObject = json_decode($order->getDhlplSettings());
            $labelType = $this->scopeConfig->getValue(self::PRINT_LABEL_CONFIG_PATH) ? $this->scopeConfig->getValue(self::PRINT_LABEL_CONFIG_PATH) : self::DEFAULT_PRINT_LABEL;
            $shipmentIds = $this->getShipmentIdFromDhlSettings($shipmentType, $shipmentSettingsObject);

            if (count($shipmentIds) > 1) {
                $archive = new ZipArchive();
                $archiveName = 'return_labels_for_' . $shipmentSettingsObject->lp . '.zip';
                $archive->open($archiveName, ZipArchive::CREATE);
                foreach ($shipmentIds as $shipmentId) {
                    $this->addFileToArchive($labelType, $shipmentId, $archive);
                }
                $archive->close();
                $fileContent = file_get_contents($archiveName);
                $fileName = $archiveName;
                $contentType = 'application/zip';
                unlink($archiveName);
            } else {
                if($shipmentSettingsObject->type == ApiHelper::SERVICE_TYPE_WEBAPI) {
                    $label = $this->apiHelper->getLabelsToPrint($labelType, $shipmentIds[0]);
                    $fileName = $label->getLabelsResult->item->labelName;
                    $contentType = $label->getLabelsResult->item->labelMimeType;
                    $fileContent = base64_decode($label->getLabelsResult->item->labelData);
                }
                if($shipmentSettingsObject->type == ApiHelper::SERVICE_TYPE_SERVICEPOINT) {
                    $label = $this->apiHelper->getLabelToPrint($labelType, $shipmentIds[0]);
                    $fileName = $label->getLabelResult->labelName;
                    $contentType = $label->getLabelResult->labelFormat;
                    $fileContent = base64_decode($label->getLabelResult->labelContent);
                }
            }

            $this->getResponse()
                ->setHeader('Content-type', $contentType)
                ->setHeader('Content-Disposition', 'attachment' . '; filename=' . $fileName );
            $this->getResponse()->setBody($fileContent);

        } catch (InputException | NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(sprintf('unable to find order %s', $oid));
        } catch (SoapFault $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $e){
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage('unable to download document');
        }
        return $resultPage;
    }

    /**
     * @param $shipmentType
     * @param $shipmentSettingsObject
     * @return array
     */
    protected function getShipmentIdFromDhlSettings($shipmentType, $shipmentSettingsObject): array
    {
        switch ($shipmentType) {
            case ShipmentType::SHIPMENT_TYPE_RETURN:
                $shipmentIds = $shipmentSettingsObject->returnlp;
                break;
            case ShipmentType::SHIPMENT_TYPE_PRIMARY:
                $shipmentIds = [$shipmentSettingsObject->lp];
                break;
            default:
                throw new InvalidShipmentTypeException($shipmentType);
        }
        return $shipmentIds;
    }

    /**
     * @param $labelType
     * @param $shipmentId
     * @param ZipArchive $zip
     * @return ZipArchive
     * @throws SoapFault
     */
    protected function addFileToArchive($labelType, $shipmentId, ZipArchive $zip): ZipArchive
    {
        $label = $this->apiHelper->getLabelsToPrint($labelType, $shipmentId);
        $fileName = $label->getLabelsResult->item->labelName;

        file_put_contents(
            '/tmp/' . $fileName,
            base64_decode($label->getLabelsResult->item->labelData)
        );
        $zip->addFile('/tmp/' . $fileName, $fileName);
        return $zip;
    }
}
