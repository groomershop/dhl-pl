<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use DHL\Dhl24pl\Helper\Shipment as ShipmentHelper;
use DHL\Dhl24pl\Helper\Api as ApiHelper;
use DHL\Dhl24pl\Model\PackageFactory;

/**
 * Class Shipment
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Shipment extends Action
{
    /**
     * @var ShipmentHelper
     */
    protected $shipmentHelper;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * Shipment constructor.
     * @param Action\Context $context
     * @param ShipmentHelper $shipmentHelper
     * @param ApiHelper $apiHelper
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        Action\Context $context,
        ShipmentHelper $shipmentHelper,
        ApiHelper $apiHelper,
        PackageFactory $packageFactory
    )
    {
        $this->shipmentHelper = $shipmentHelper;
        $this->apiHelper = $apiHelper;
        $this->packageFactory = $packageFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('DHL_Dhl24pl::dhlpl');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $name = $this->getRequest()->getParam('name');
        $packages = $this->getPackages();

        $result = [];

        if (isset($packages[$name])) {
            $result['shipment'] = $packages[$name];
        }

        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * @return array
     */
    protected function getPackages(): array
    {
        $packages = $this->packageFactory->create()->getCollection()->getData();
        $data = [];

        foreach ($packages as $package) {
            $data[$package['name']] = $package;
        }

        return $data;
    }

}
