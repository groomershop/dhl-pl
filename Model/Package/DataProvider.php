<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Package;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use DHL\Dhl24pl\Model\PackageFactory;
use DHL\Dhl24pl\Model\Request\DHLRequestBuilder;

/**
 * Class DataProvider
 * @package DHL\Dhl24pl\Model\Sender
 */
class DataProvider extends MagentoDataProvider
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param PackageFactory $packageFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        ScopeConfigInterface $scopeConfig,
        PackageFactory $packageFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->packageFactory = $packageFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    /**
     * @return array|void
     */
    public function getData()
    {
        $id = $this->request->getParam('id');

        if($id) {
            $packageFactory = $this->packageFactory->create();
            $data[$id] = $this->parseData($packageFactory->load($id)->getData());

            return $data;
        }
    }

    /**
     * @param $data
     * @return array
     */
    protected function parseData($data): array
    {
        $item = [];
        if(isset($data)) {
            $item['package']['id'] = $data['id'];
            $item['package']['name'] = $data['name'];
            $item['packagetype']['weight'] = $data['weight'];
            $item['packagetype']['width'] = $data['width'];
            $item['packagetype']['height'] = $data['height'];
            $item['packagetype']['lenght'] = $data['lenght'];
            $item['packagetype']['quantity'] = $data['quantity'];
            $item['packagetype']['non_standard'] = (bool)$data['non_standard'];
            $item['packagetype']['package_type'] = $data['package_type'];
            $item['services']['insurance_price'] = $data['insurance_price'];
            $item['services']['cod_price'] = $data['cod_price'];
            $item['services']['insurance'] = (bool)$data['insurance'];
            $item['services']['cod'] = (bool)$data['cod'];
            $item['services']['return_on_delivery'] = (bool)$data['return_on_delivery'];
            $item['services']['proof_of_delivery'] = (bool)$data['proof_of_delivery'];
            $item['services']['delivery_to_neighbour'] = (bool)$data['delivery_to_neighbour'];
            $item['services']['delivery_to_lm'] = (bool)$data['delivery_to_lm'];
            $item['services']['delivery_evening'] = (bool)$data['delivery_evening'];
            $item['services']['delivery_on_saturday'] = (bool)$data['delivery_on_saturday'];
            $item['services']['pickup_on_saturday'] = (bool)$data['pickup_on_saturday'];
            $item['services']['self_collect'] = (bool)$data['self_collect'];
            $item['services']['predelivery_information'] = (bool)$data['predelivery_information'];
            $item['package_additional']['content'] = $data['content'];
            $item['package_additional']['costs_center'] = $data['costs_center'];
            $item['package_additional']['comment'] = $data['comment'];
            $item['package']['is_courier'] = (bool)$data['is_courier'];
            $item['package']['is_default'] = (bool)$data['is_default'];
            $item['package']['sender'] = $data['sender'];
            $item['package']['package_type'] = $data['product_type'];
            $item['package']['payer'] = $data['payer'];
            $item['package']['regular_pickup'] = true;
            if($this->scopeConfig->getValue(DHLRequestBuilder::REGULAR_PICKUP_CONFIG)) {
                $item['package']['regular_pickup'] = false;
            }

            $item['package']['is_default_enable'] = false;
            if($this->checkIfPackageCanBeDefault() || $item['package']['is_default'] == true) {
                $item['package']['is_default_enable'] = true;
            }

        }

        return $item;
    }

    /**
     * @return bool
     */
    protected function checkIfPackageCanBeDefault(): bool
    {
        $packages = $packageFactory = $this->packageFactory->create()->getCollection();

        $isEnableDefault = true;
        foreach ($packages as $package) {
            if($package->getIsDefault()) {
                $isEnableDefault = false;
            }
        }

        return $isEnableDefault;
    }
}
