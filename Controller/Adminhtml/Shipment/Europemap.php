<?php
namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderRepositoryInterface;

class Europemap extends \Magento\Backend\App\Action
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
        return $resultPage;
    }

    public function execute() {
        $country = $this->getRequest()->getParam('country');
        $type = $this->getRequest()->getParam('type');
        $resultPage = $this->_initAction();

        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_europemap')->setCountryEk($country);
        $resultPage->getLayout()->getBlock('dhl_dhl24pl_shipment_europemap')->setTypeEk($type);


        return $resultPage;
    }
}
