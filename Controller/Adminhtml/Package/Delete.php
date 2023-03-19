<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Package;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use DHL\Dhl24pl\Model\PackageFactory;

/**
 * Class Delete
 * @package DHL\Dhl24pl\Controller\Adminhtml\Package
 */
class Delete extends Action
{
    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        Context $context,
        PackageFactory $packageFactory
    )
    {
        $this->packageFactory = $packageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $packageFactory = $this->packageFactory->create();
            $packageFactory->load($id);
            if($packageFactory->delete()) {
                $this->messageManager->addSuccessMessage( __('Szablon paczki usunięty!') );
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage( __('Nie udało się usunąć szablonu paczki!') );
        }
    }
}
