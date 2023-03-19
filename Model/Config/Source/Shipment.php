<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use DHL\Dhl24pl\Helper\Api AS ApiHelper;
use DHL\Dhl24pl\Model\PackageFactory;

/**
 * Class Shipment
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class Shipment implements OptionSourceInterface
{
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
     * @param ApiHelper $apiHelper
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        ApiHelper $apiHelper,
        PackageFactory $packageFactory
    )
    {
        $this->apiHelper = $apiHelper;
        $this->packageFactory = $packageFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $packages = $this->getPackages();
        $data = [];
        $data[] = ['value' => '-- wybierz --', 'label' => '-- wybierz --'];

        if (isset($packages) && is_array($packages) && count($packages) > 0) {
            foreach ($packages as $key => $value) {
                $data[] = [
                    'value' => $value['name'], 'label' => $value['name']
                ];
            }
        }

        return $data;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $packages = $this->getPackages();
        $data = [];
        $data['-- wybierz --'] = '-- wybierz --';

        if (isset($packages) && is_array($packages) && count($packages) > 0) {
            foreach ($packages as $key => $value) {
                $data[$value['name']] = $value['name'];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getPackages(): array
    {
        return $this->packageFactory->create()->getCollection()->getData();
    }
}
