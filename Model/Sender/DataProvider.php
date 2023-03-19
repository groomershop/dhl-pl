<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Sender;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use DHL\Dhl24pl\Model\SenderFactory;

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
     * @var SenderFactory
     */
    protected $senderFactory;

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
     * @param SenderFactory $senderFactory
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
        SenderFactory $senderFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->senderFactory = $senderFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    /**
     * @return array|void
     */
    public function getData()
    {
        $id = $this->request->getParam('id');

        if($id) {
            $senderFactory = $this->senderFactory->create();
            $data[$id] = ['sender' => $senderFactory->load($id)->getData()];

            return $data;
        }
    }
}
