<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Sender;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class Edit
 * @package DHL\Dhl24pl\Controller\Adminhtml\Sender
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SenderFactory
     */
    protected $senderFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        SenderFactory $senderFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->senderFactory = $senderFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $data = $this->_request->getParams();

        if(isset($data['sender'])) {
            $senderFactory = $this->senderFactory->create();
            if(empty($data['sender']['id'])) {
                unset($data['sender']['id']);
            }
            $senderFactory->setData($data['sender']);

            if($senderFactory->save()) {
                $this->messageManager->addSuccessMessage( __('Dodany nadawca !') );
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        }

        return $resultPage;
    }
}
