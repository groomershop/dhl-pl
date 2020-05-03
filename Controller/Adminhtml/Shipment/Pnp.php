<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderRepositoryInterface;

class Pnp extends \Magento\Backend\App\Action
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
    $resultPage->getConfig()->getTitle()->prepend('Pnp');
    $resultPage->getConfig()->getTitle()->set('DHL24');

    return $resultPage;
}

    public function execute() {

        $pnpHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Pnp');
        $apiHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Api');

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if ( $pnpHelper->validate($data)) {
                try {
                    if ($data['kind'] == 'WEBAPI') {
                        $result = $apiHelper->generateWebapiPnp($data['date'], $data['type']);
                        header('Content-type: ' . $result->getPnpResult->fileMimeType);
                        header('Content-Disposition: attachment; filename="' . $result->getPnpResult->fileName . '"');
                        echo base64_decode($result->getPnpResult->fileData);
                    } else {
                        $result = $apiHelper->generateServicepointPnp($data['date']);
                        header('Content-type: ' . $result->getPnpResult->labelFormat);
                        header('Content-Disposition: attachment; filename="' . $result->getPnpResult->labelName . '"');
                        echo base64_decode($result->getPnpResult->labelContent);
                    }
                } catch (\Exception $e) {
                    $pnpHelper->setError($e->getMessage());
                }
            }
        }

        if ($pnpHelper->isError()) {
            $this->messageManager->addError($pnpHelper->getError());
        }
        $resultPage = $this->_initAction();
        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_pnp')->setPnpValues($data);

        return $resultPage;
    }
}
