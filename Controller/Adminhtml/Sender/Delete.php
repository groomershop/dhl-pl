<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Sender;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class Delete
 * @package DHL\Dhl24pl\Controller\Adminhtml\Package
 */
class Delete extends Action
{
    /**
     * @var SenderFactory
     */
    protected $senderFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        Context $context,
        SenderFactory $senderFactory
    )
    {
        $this->senderFactory = $senderFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $senderFactory = $this->packageFactory->create();
            $senderFactory->load($id);
            if($senderFactory->delete()) {
                $this->messageManager->addSuccessMessage( __('Nadawca usunięty!') );
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage( __('Nie udało się usunąć nadawcy!') );
        }
    }
}
