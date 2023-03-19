<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Package;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use DHL\Dhl24pl\Model\PackageFactory;

/**
 * Class Edit
 * @package DHL\Dhl24pl\Controller\Adminhtml\Package
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        PackageFactory $packageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->packageFactory = $packageFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $data = $this->_request->getParams();

        if(isset($data['package'])) {
            $packageFactory = $this->packageFactory->create();
            $packageFactory->setData($this->parseFormData($data));

            if($packageFactory->save()) {
                $this->messageManager->addSuccessMessage( __('Dodany szablon przeszyłki !') );
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        }

        return $resultPage;
    }

    /**
     * @param $data
     * @return array
     */
    protected function parseFormData($data)
    {
        $item = [];
        if(isset($data['package'])) {
            if($data['package']['id']) {
            $item['id'] = $data['package']['id'];
            }
            $item['name'] = $data['package']['name'];
            $item['weight'] = $data['packagetype']['weight'];
            $item['width'] = $data['packagetype']['width'];
            $item['height'] = $data['packagetype']['height'];
            $item['lenght'] = $data['packagetype']['lenght'];
            $item['quantity'] = $data['packagetype']['quantity'];
            $item['non_standard'] = ($data['packagetype']['non_standard'] == 'true') ? 1 : 0;
            $item['package_type'] = $data['packagetype']['package_type'];
            $item['insurance_price'] = $data['services']['insurance_price'];
            $item['insurance'] = ($data['services']['insurance'] == 'true') ? 1 : 0;
            $item['return_on_delivery'] = ($data['services']['return_on_delivery'] == 'true') ? 1 : 0;
            $item['proof_of_delivery'] = ($data['services']['proof_of_delivery'] == 'true') ? 1 : 0;
            $item['delivery_to_neighbour'] = ($data['services']['delivery_to_neighbour'] == 'true') ? 1 : 0;
            $item['delivery_to_lm'] = ($data['services']['delivery_to_lm'] == 'true') ? 1 : 0;
            $item['delivery_evening'] = ($data['services']['delivery_evening'] == 'true') ? 1 : 0;
            $item['delivery_on_saturday'] = ($data['services']['delivery_on_saturday'] == 'true') ? 1 : 0;
            $item['pickup_on_saturday'] = ($data['services']['pickup_on_saturday'] == 'true') ? 1 : 0;
            $item['self_collect'] = ($data['services']['self_collect'] == 'true') ? 1 : 0;
            $item['predelivery_information'] = ($data['services']['predelivery_information'] == 'true') ? 1 : 0;
            $item['content'] = $data['package_additional']['content'];
            $item['costs_center'] = $data['package_additional']['costs_center'];
            $item['comment'] = $data['package_additional']['comment'];
            $item['is_courier'] = ($data['package']['is_courier'] == 'true') ? 1 : 0;
            $item['is_default'] = ($data['package']['is_default'] == 'true') ? 1 : 0;
            $item['sender'] = $data['package']['sender'];
            $item['product_type'] = $data['package']['package_type'];
            $item['payer'] = $data['package']['payer'];
        }

        return $item;
    }
}
