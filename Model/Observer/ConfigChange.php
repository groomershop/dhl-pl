<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use DHL\Dhl24pl\Model\ResourceModel\Package\CollectionFactory as PackageCollectionFactory;
use DHL\Dhl24pl\Model\PackageFactory;

/**
 * Class ConfigChange
 * @package DHL\Dhl24pl\Model\Observer
 */
class ConfigChange implements ObserverInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var PackageCollectionFactory
     */
    protected $packageCollectionFactory;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param PackageCollectionFactory $packageCollectionFactory
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        RequestInterface $request,
        PackageCollectionFactory $packageCollectionFactory,
        PackageFactory $packageFactory
    ) {
        $this->request = $request;
        $this->packageCollectionFactory = $packageCollectionFactory;
        $this->packageFactory = $packageFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $request = $this->request->getParam('groups');

        $packages = $this->packageFactory->create()->getCollection();
        $packageFactory = $this->packageFactory->create();
        foreach ($packages as $package) {
            try {
                $packageFactoryData = $packageFactory->load($package->getId());
                $isCourier = ($request['api']['fields']['regular_pickup']['value'] == '0') ? 1 : 0;
                $packageFactoryData->setData('is_courier', $isCourier);
                $packageFactoryData->save();
            } catch (Exception $e) {

            }
        }
    }
}
