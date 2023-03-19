<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Helper;

use DHL\Dhl24pl\Helper\Shipment as ShipmentHelper;
use DHL\Dhl24pl\Model\Config\DHL24Config;
use DHL\Dhl24pl\Model\Config\DHL24ReturnShipmentConfig;
use DHL\Dhl24pl\Model\Logger\ApiLogger;
use DHL\Dhl24pl\Model\Request\DHLRequestBuilder;
use DHL\Dhl24pl\Model\Shipment\ApiServiceType;
use Exception;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Model\UrlInterface AS BackendUrlInterface;
use SoapClient;
use SoapFault;
use stdClass;
use DHL\Dhl24pl\Model\PackageFactory;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class Api
 * @package DHL\Dhl24pl\Helper
 */
class Api extends AbstractHelper
{
    const DHL_WEBAPI2 = 'https://sandbox.dhl24.com.pl/webapi2';
    const DHL_SERVICEPOINTAPI = 'https://sandbox.dhl24.com.pl/servicepoint';

    const MAP_URL = 'https://parcelshop.dhl.pl/mapa';

    const CONFIG_RETURN_ENABLE = 'dhl24pl/return/enable';

    const SERVICE_TYPE_WEBAPI = 'WEBAPI';
    const SERVICE_TYPE_SERVICEPOINT = 'SERVICEPOINT';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DHL24Config
     */
    protected $dhlConfig;

    /**
     * @var DHL24ReturnShipmentConfig
     */
    protected $returnShipmentConfig;

    /**
     * @var DHLRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var Shipment
     */
    protected $shipmentHelper;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var BackendUrlInterface
     */
    protected $backendUrl;

    /**
     * @var ApiLogger
     */
    protected $logger;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;

    /**
     * @var SenderFactory
     */
    protected $senderFactory;

    /**
     * Api constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param DHL24Config $dhlConfig
     * @param DHL24ReturnShipmentConfig $returnShipmentConfig
     * @param DHLRequestBuilder $requestBuilder
     * @param Shipment $shipmentHelper
     * @param BackendUrlInterface $backendUrl
     * @param CacheInterface $cache
     * @param ApiLogger $logger
     * @param PackageFactory $packageFactory
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        DHL24Config $dhlConfig,
        DHL24ReturnShipmentConfig $returnShipmentConfig,
        DHLRequestBuilder $requestBuilder,
        ShipmentHelper $shipmentHelper,
        BackendUrlInterface $backendUrl,
        CacheInterface $cache,
        ApiLogger $logger,
        PackageFactory $packageFactory,
        SenderFactory $senderFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->dhlConfig = $dhlConfig;
        $this->returnShipmentConfig = $returnShipmentConfig;
        $this->requestBuilder = $requestBuilder;
        $this->shipmentHelper = $shipmentHelper;
        $this->backendUrl = $backendUrl;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->packageFactory = $packageFactory;
        $this->senderFactory = $senderFactory;

        parent::__construct($context);
    }

    /**
     * @param $post
     * @param $magentoDHLConfig
     * @return mixed
     * @throws SoapFault
     */
    public function createShipment($post, $magentoDHLConfig)
    {
        $params = $this->requestBuilder->buildUniversalShipmentRequest($post, $magentoDHLConfig);
        $serviceType = $this->requestBuilder->shouldUseServicePointApi($post) == true ? ApiServiceType::SERVICE_POINT_API() : ApiServiceType::WEB_API2();
        $instance = $this->requestBuilder->buildSoapClientInstance($serviceType);
        $this->logRequest($params, "createShipment");
        return $instance->createShipment($params);
    }

    /**
     * @param $post
     * @param $magentoData
     * @return stdClass
     * @throws SoapFault
     */
    public function createReturnShipment($post, $magentoData): stdClass
    {
        $returnShipmentParams = array_merge(
            $this->dhlConfig->getApiAuthorizationArray(),
            $this->requestBuilder->buildReturnShipmentRequest($post, $magentoData)
        );

        $instance = $this->requestBuilder->buildSoapClientInstance(ApiServiceType::WEB_API2());
        $this->logRequest($returnShipmentParams, "createReturnShipment");
        return $instance->createShipmentReturn($returnShipmentParams);
    }

    /**
     * @return array
     */
    public function getDataForShipment(): array
    {
        $data = [];

        $packages = $this->packageFactory->create()->getCollection()->getData();
        $senders = $this->senderFactory->create()->getCollection()->getData();

        $data['data']['dropOffType'] = DHLRequestBuilder::REQUEST_COURIER;
        $data['data']['courier'] = true;
        $data['data']['labelType'] = $this->scopeConfig->getValue(DHLRequestBuilder::PRINT_LABEL_CONFIG_PATH) ? $this->scopeConfig->getValue(DHLRequestBuilder::PRINT_LABEL_CONFIG_PATH) : DHLRequestBuilder::DEFAULT_PRINT_LABEL;

        if($this->scopeConfig->getValue(DHLRequestBuilder::REGULAR_PICKUP_CONFIG)) {
            $data['data']['dropOffType'] = DHLRequestBuilder::REGULAR_PICKUP;
            $data['data']['courier'] = false;
        }

        $item = [];
        foreach ($packages as $package) {
            $item[$package['name']]['name'] = $package['name'];
            $item[$package['name']]['weight'] = $package['weight'];
            $item[$package['name']]['width'] = $package['width'];
            $item[$package['name']]['height'] = $package['height'];
            $item[$package['name']]['lenght'] = $package['lenght'];
            $item[$package['name']]['name'] = $package['name'];
            $item[$package['name']]['quantity'] = $package['quantity'];
            $item[$package['name']]['nonStandard'] = $package['non_standard'];
            $item[$package['name']]['packageType'] = $package['package_type'];
            $item[$package['name']]['insuranceValue'] = $package['insurance_price'];
            $item[$package['name']]['insurance'] = (bool)$package['insurance'];
            $item[$package['name']]['collectOnDelivery'] = (bool)$package['cod'];
            $item[$package['name']]['collectOnDeliveryValue'] = $package['cod_price'];
            $item[$package['name']]['returnOnDelivery'] = (bool)$package['return_on_delivery'];
            $item[$package['name']]['proofOfDelivery'] = (bool)$package['proof_of_delivery'];
            $item[$package['name']]['deliveryToNeighbour'] = (bool)$package['delivery_to_neighbour'];
            $item[$package['name']]['deliveryToLM'] = (bool)$package['delivery_to_lm'];
            $item[$package['name']]['deliveryEvening'] = (bool)$package['delivery_evening'];
            $item[$package['name']]['deliveryOnSaturday'] = (bool)$package['delivery_on_saturday'];
            $item[$package['name']]['pickupOnSaturday'] = (bool)$package['pickup_on_saturday'];
            $item[$package['name']]['selfCollect'] = (bool)$package['self_collect'];
            $item[$package['name']]['content'] = $package['content'];
            $item[$package['name']]['costsCenter'] = $package['costs_center'];
            $item[$package['name']]['comment'] = $package['comment'];
            $item[$package['name']]['senderName'] = $package['sender'];
            $item[$package['name']]['default'] = (bool)$package['is_courier'];
            $item[$package['name']]['courier'] = (bool)$package['is_default'];
            $item[$package['name']]['payer'] = $package['payer'];
            $item[$package['name']]['product'] = $package['product_type'];
        }

        $data['data']['shipments'] = $item;

        $item = [];
        foreach ($senders as $sender) {
            $item[$sender['name']] = $sender;
            $item[$sender['name']]['personName'] = $sender['name'];
            $item[$sender['name']]['phoneNumber'] = $sender['phone'];
            $item[$sender['name']]['houseNumber'] = $sender['street_number'];
            $item[$sender['name']]['apartmentNumber'] = $sender['house_number'];
        }

        $data['data']['senders'] = $item;

        return $data;

    }

    /**
     * @param $lp
     * @return mixed
     * @throws SoapFault
     */
    public function removeServicePointShipment($lp) {
        $instance = new SoapClient(
            self::DHL_SERVICEPOINTAPI,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$instance ) {
            throw new Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->scopeConfig->getValue('dhl24pl/servicepoint/login', ScopeInterface::SCOPE_STORE);
        $pass = $this->scopeConfig->getValue('dhl24pl/servicepoint/password', ScopeInterface::SCOPE_STORE);
        $params = array(
            'shipment' => array(
                'authData' => array('username' => $login, 'password' => $pass),
                'shipment' => $lp
            )
        );
        $this->logRequest($params, "removeServicePointShipment");
        return $instance->deleteShipment($params);
    }

    /**
     * @param $lp
     * @param $dispatchNotificationNumber
     * @return mixed
     * @throws SoapFault
     */
    public function removeWebapiShipment($lp, $dispatchNotificationNumber) {
        $instance = new SoapClient(
            self::DHL_WEBAPI2,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$instance ) {
            throw new Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->scopeConfig->getValue('dhl24pl/webapi/login', ScopeInterface::SCOPE_STORE);
        $pass = $this->scopeConfig->getValue('dhl24pl/webapi/password', ScopeInterface::SCOPE_STORE);
        $params = array(
            'authData' => array('username' => $login, 'password' => $pass),
            'shipment' => array(
                'shipmentIdentificationNumber' => $lp,
                'dispatchIdentificationNumber' => $dispatchNotificationNumber
            )
        );
        if ($dispatchNotificationNumber != null) {
            $params['shipment']['dispatchIdentificationNumber'] = $dispatchNotificationNumber;
        }
        $this->logRequest($params, "removeWebapiShipment");
        return $instance->deleteShipment($params);
    }

    /**
     * @return string
     */
    public function getLmmap() {
        return self::MAP_URL;
    }

    /**
     * @return stdClass
     * @throws SoapFault
     */
    public function getInternationalParams(): stdClass {
        $instance = $this->requestBuilder->buildSoapClientInstance(ApiServiceType::WEB_API2());
        $params = $this->dhlConfig->getApiAuthorizationArray();

        return $instance->getInternationalParams($params);
    }

    /**
     * @param $labelType
     * @param $shipmentId
     * @return mixed
     * @throws SoapFault
     */
    public function getLabelsToPrint($labelType, $shipmentId)
    {
        $instance = $this->requestBuilder->buildSoapClientInstance(ApiServiceType::WEB_API2());

        $params = $this->dhlConfig->getApiAuthorizationArray();
        $params['itemsToPrint'] = [
            'item' => [
                'labelType' => $labelType,
                'shipmentId' => $shipmentId
            ]
        ];

        return $instance->getLabels($params);
    }

    /**
     * @param $labelType
     * @param $shipmentId
     * @return mixed
     * @throws SoapFault
     */
    public function getLabelToPrint($labelType, $shipmentId)
    {
        $instance = $this->requestBuilder->buildSoapClientInstance(ApiServiceType::SERVICE_POINT_API());
        $params = [
            'structure' => [
                'authData' => $this->dhlConfig->getApiAuthorizationArray(true)['authData'],
                'type' => $labelType,
                'shipment' => $shipmentId
            ]
        ];
        return $instance->getLabel($params);
    }

    /**
     * @param $url
     * @return string
     */
    public function getBackendUrl($url): string
    {
        return $this->backendUrl->getUrl($url);
    }

    /**
     * @param $code
     * @return mixed
     * @throws SoapFault
     */
    public function getPostalCodeServicesByPostCode($code) {
        $instance = $this->requestBuilder->buildSoapClientInstance(ApiServiceType::WEB_API2());

        if( !$instance ) {
            throw new Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->scopeConfig->getValue('dhl24pl/webapi/login', ScopeInterface::SCOPE_STORE);
        $pass = $this->scopeConfig->getValue('dhl24pl/webapi/password', ScopeInterface::SCOPE_STORE);
        $params = array(
            "authData" => array('username' => $login, 'password' => $pass),
            'postCode' => str_replace('-', '', $code),
            'pickupDate' => date('Y-m-d')
        );
        $result = $instance->getPostalCodeServices( $params );

        return $result;
    }

    public function shouldUseServicePointApi(): bool
    {
        return $this->requestBuilder->shouldUseServicePointApi([]);
    }

    /**
     * @param array $params
     * @param string $requestName
     */
    protected function logRequest(array $params, string $requestName): void
    {
        $this->logger->info(sprintf("%s: %s", $requestName, json_encode($params)));
    }

    /**
     * @return bool
     */
    public function shouldCreateReturn(): bool
    {
        $configValue = (int)$this->scopeConfig->getValue(self::CONFIG_RETURN_ENABLE);

        if($configValue) {
            return true;
        }
        if(!$configValue) {
            return false;
        }
    }
}
