<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Request;

use DateInterval;
use DateTime;
use DHL\Dhl24pl\Helper\Shipment;
use DHL\Dhl24pl\Model\Config\DHL24Config;
use DHL\Dhl24pl\Model\Config\DHL24ReturnShipmentConfig;
use DHL\Dhl24pl\Model\Shipment\ApiServiceType;
use DHL\Dhl24pl\Model\Shipment\ReturnServiceType;
use Exception;
use SoapClient;
use SoapFault;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class DHLRequestBuilder
 * @package DHL\Dhl24pl\Model\Request
 */
class DHLRequestBuilder
{
    const DHL_WEBAPI2 = 'https://sandbox.dhl24.com.pl/webapi2';
    const DHL_MAGENTOAPI = 'https://sandbox.dhl24.com.pl/magentoapi';
    const DHL_SERVICEPOINTAPI = 'https://sandbox.dhl24.com.pl/servicepoint';
    const DHL_PRODUCTION_WEBAPI2 = 'https://dhl24.com.pl/webapi2';
    const DHL_PRODUCTION_MAGENTOAPI = 'https://dhl24.com.pl/magentoapi';
    const DHL_PRODUCTION_SERVICEPOINTAPI = 'https://dhl24.com.pl/servicepoint';
    const DHL_API_PRODUCTION_TEST_TYPE = 'dhl24pl/api/api_type';
    const SOAP_CONNECTION_TIMEOUT = 3;
    const RETURN_SHIPPING_PAYMENT_TYPE = 'RECEIVER';
    const RETURN_PAYMENT_TYPE = 'BANK_TRANSFER';
    const INTERNATIONAL_PACKAGE_CODE = 'EK';
    const SERVICE_POINT_SERVICE_TYPE = 'LM';

    const REGULAR_PICKUP = 'REGULAR_PICKUP';
    const REGULAR_PICKUP_CONFIG = 'dhl24pl/api/regular_pickup';

    const REQUEST_COURIER = 'REQUEST_COURIER';

    const PRINT_LABEL_CONFIG_PATH = 'dhl24pl/administration/print_label';
    const DEFAULT_PRINT_LABEL = 'BLP';

    protected $useServicePointApi;

    /**
     * @var Shipment
     */
    protected $shipmentHelper;

    /**
     * @var DHL24ReturnShipmentConfig
     */
    protected $returnShipmentConfig;

    /**
     * @var DHL24Config
     */
    protected $dhlConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * DHLRequestBuilder constructor.
     * @param Shipment $shipmentHelper
     * @param DHL24ReturnShipmentConfig $returnShipmentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param DHL24Config $dhlConfig
     */
    public function __construct(
        Shipment $shipmentHelper,
        DHL24ReturnShipmentConfig $returnShipmentConfig,
        ScopeConfigInterface $scopeConfig,
        DHL24Config $dhlConfig
    )
    {
        $this->shipmentHelper = $shipmentHelper;
        $this->returnShipmentConfig = $returnShipmentConfig;
        $this->scopeConfig = $scopeConfig;
        $this->dhlConfig = $dhlConfig;
    }

    /**
     * @param ApiServiceType $serviceType
     * @return SoapClient
     * @throws SoapFault
     */
    public function buildSoapClientInstance(ApiServiceType $serviceType): SoapClient
    {
        $context = stream_context_create(array('http' => array('header' => 'DHLApiOrgin: MAGENTO2-FINTURE')));
        $_instance = new SoapClient(
            $this->getWsdlUrlFromServiceType($serviceType),
            array(
                'keep-alive' => true,
                'connection_timeout' => self::SOAP_CONNECTION_TIMEOUT,
                'stream_context' => $context
            )
        );

        if( !$_instance ) {
            throw new Exception('Connection error');
        }

        return $_instance;
    }

    /**
     * @param $post
     * @param $magentoData
     * @return array
     */
    public function buildShipmentRequest($post, $magentoData): array
    {
        $shipment = [];
        $shipmentInfo = [];
        $shipmentInfo['dropOffType'] = $magentoData['data']['dropOffType'];
        if($this->scopeConfig->getValue(self::REGULAR_PICKUP_CONFIG)) {
            $shipmentInfo['dropOffType'] = self::REGULAR_PICKUP;
        }

        if (isset($post['package_product_type']['product_type'])) {
            $shipmentInfo['serviceType'] = $post['package_product_type']['product_type'];
        }

        $billing = [];
        if (isset($post['payer']['payerType'])) {
            if ($post['payer']['payerType'] == 'N') {
                $billing['shippingPaymentType'] = 'SHIPPER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            } elseif ($post['payer']['payerType'] == 'O') {
                $billing['shippingPaymentType'] = 'RECEIVER';
                $billing['paymentType'] = 'CASH';
            } elseif($post['payer']['payerType'] == 'T') {
                $billing['shippingPaymentType'] = 'USER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            }
        }

        if (isset($post['sender']['sap'])) {
            $billing['billingAccountNumber'] = $post['sender']['sap'];
        }

        if (isset($post['package_additional']['costsCenter'])) {
            $billing['costsCenter'] = $post['package_additional']['costsCenter'];
        }

        $shipmentInfo['billing'] = $billing;
        $shipmentInfo['labelType'] = $magentoData['data']['labelType'];

        $specialServices = [];
        if (isset($post['package_additional']['insurance']) && $post['package_additional']['insurance'] == 'true') {
            $item = [];
            $item['serviceType'] = 'UBEZP';
            if (isset($post['package_additional']['insuranceValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['package_additional']['insuranceValue']);
            }
            $specialServices[] = $item;
        }

        if (isset($post['collectOnDelivery']) && $post['collectOnDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'COD';
            if (isset($post['package_additional']['collectOnDeliveryValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['package_additional']['collectOnDeliveryValue']);
            }
            $item['collectOnDeliveryForm'] = 'BANK_TRANSFER';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['predeliveryInformation']) && $post['package_additional']['predeliveryInformation'] == 'true') {
            $item = [];
            $item['serviceType'] = 'PDI';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['returnOnDelivery']) && $post['package_additional']['returnOnDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'ROD';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['proofOfDelivery']) && $post['package_additional']['proofOfDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'POD';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['deliveryEvening']) && $post['package_additional']['deliveryEvening'] == 'true') {
            $item = [];
            $item['serviceType'] = '1722';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['deliveryOnSaturday']) && $post['package_additional']['deliveryOnSaturday'] == 'true') {
            $item = [];
            $item['serviceType'] = 'SOBOTA';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['pickupOnSaturday']) && $post['package_additional']['pickupOnSaturday'] == 'true') {
            $item = [];
            $item['serviceType'] = 'NAD_SOBOTA';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['selfCollect']) && $post['package_additional']['selfCollect'] == 'true') {
            $item = [];
            $item['serviceType'] = 'ODB';
            $specialServices[] = $item;
        }

        if (isset($post['package_additional']['deliveryToNeighbour']) && $post['package_additional']['deliveryToNeighbour'] == 'true') {
            $item = [];
            $item['serviceType'] = 'SAS';
            $specialServices[] = $item;
        }

        $shipmentInfo['specialServices'] = $specialServices;
        $shipmentTime = [];

        if (isset($post['package_shipment']['shipmentDate'])) {
            $shipmentTime['shipmentDate'] = $post['package_shipment']['shipmentDate'];
        }

        if (isset($post['package_shipment']['shipmentStartHour'])) {
            $shipmentTime['shipmentStartHour'] = $post['package_shipment']['shipmentStartHour'];
        }

        if (isset($post['package_shipment']['shipmentEndHour'])) {
            $shipmentTime['shipmentEndHour'] = $post['package_shipment']['shipmentEndHour'];
        }

        $shipmentInfo['shipmentTime'] = $shipmentTime;
        $shipment['shipmentInfo'] = $shipmentInfo;
        if (isset($post['package']['content'])) {
            $shipment['content'] = $post['package']['content'];
        }

        if (isset($post['package_additional']['comment'])) {
            $shipment['comment'] = $post['package_additional']['comment'];
        }

        if (isset($post['package_additional']['reference'])) {
            $shipment['reference'] = $post['package_additional']['reference'];
        }

        $ship = [];
        $shipper = [];
        $shipperPreaviso = [];
        $shipperContact = [];
        if (isset($post['sender']['personName'])) {
            $shipperPreaviso['personName'] = $post['sender']['personName'];
            $shipperContact['personName'] = $post['sender']['personName'];

        }

        if (isset($post['sender']['phoneNumber'])) {
            $shipperPreaviso['phoneNumber'] = $this->shipmentHelper->clearPhone($post['sender']['phoneNumber']);
            $shipperContact['phoneNumber'] = $this->shipmentHelper->clearPhone($post['sender']['phoneNumber']);

        }

        if (isset($post['sender']['emailAddress'])) {
            $shipperPreaviso['emailAddress'] = $post['sender']['emailAddress'];
            $shipperContact['emailAddress'] = $post['sender']['emailAddress'];
        }

        $shipper['preaviso'] = $shipperPreaviso;
        $shipper['contact'] = $shipperContact;
        $shipperAddress = [];
        if (isset($post['payer']['payerType'])) {
            $shipperAddress['name'] = $post['sender']['personName'];
        }

        if (isset($post['sender']['postcode'])) {
            $shipperAddress['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['sender']['postcode']);
        }

        if (isset($post['sender']['city'])) {
            $shipperAddress['city'] = $post['sender']['city'];
        }

        if (isset($post['sender']['street'])) {
            $shipperAddress['street'] = $post['sender']['street'];
        }

        if (isset($post['sender']['houseNumber'])) {
            $shipperAddress['houseNumber'] = $post['sender']['houseNumber'];
        }

        if (isset($post['sender']['apartmentNumber'])) {
            $shipperAddress['apartmentNumber'] = $post['sender']['apartmentNumber'];
        }

        $shipper['address'] = $shipperAddress;
        $ship['shipper'] = $shipper;
        $receiver = [];
        $receiverPreaviso = [];
        $receiverContact = [];
        if (isset($post['recipient']['personName'])) {
            $receiverPreaviso['personName'] = $post['recipient']['personName'];
            $receiverContact['personName'] = $post['recipient']['personName'];

        }

        if (isset($post['recipient']['phoneNumber'])) {
            $receiverPreaviso['phoneNumber'] = $this->shipmentHelper->clearPhone($post['recipient']['phoneNumber']);
            $receiverContact['phoneNumber'] = $this->shipmentHelper->clearPhone($post['recipient']['phoneNumber']);

        }

        if (isset($post['recipient']['emailAddress'])) {
            $receiverPreaviso['emailAddress'] = $post['recipient']['emailAddress'];
            $receiverContact['emailAddress'] = $post['recipient']['emailAddress'];
        }

        $receiver['preaviso'] = $receiverPreaviso;
        $receiver['contact'] = $receiverContact;
        $receiverAddress = [];
        if (isset($post['recipient']['personName'])) {
            $receiverAddress['name'] = $post['recipient']['personName'];
        }

        if (isset($post['recipient']['postcode'])) {
            $receiverAddress['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['recipient']['postcode']);
        }

        if (isset($post['recipient']['city'])) {
            $receiverAddress['city'] = $post['recipient']['city'];
        }

        if (isset($post['recipient']['street'])) {
            $receiverAddress['street'] = $post['recipient']['street'];
            if (is_array($post['recipient']['street'])) {
                $receiverAddress['street'] = $post['recipient']['street'][0];
            }
        }

        if (isset($post['recipient']['houseNumber'])) {
            $receiverAddress['houseNumber'] = $post['recipient']['houseNumber'];
        }

        if (isset($post['recipient']['apartmentNumber'])) {
            $receiverAddress['apartmentNumber'] = $post['recipient']['apartmentNumber'];
        }

        if (isset($post['recipient']['addressType'])) {
            $type = 'C';
            if($post['recipient']['addressType'] == 'true') {
                $type = 'B';
            }
            $receiverAddress['addressType'] = $type;
        }

        if (isset($post['product']) && $post['product'] == 'EK') {
            if (isset($post['ek_country'])) {
                $receiverAddress['country'] = $post['ek_country'];
            }

            if (isset($post['ek_packstation']) && !empty($post['ek_packstation'])) {
                $receiverAddress['houseNumber'] = $post['ek_packstation'];
                $receiverAddress['isPackstation'] = true;
            } else {
                $receiverAddress['isPackstation'] = false;
            }

            $receiverAddress['isPostfiliale'] = false;
            if (isset($post['ek_postnummer'])) {
                $receiverAddress['postnummer'] = $post['ek_postnummer'];
            }
        } else {
            $receiverAddress['country'] = 'PL';
        }

        $receiver['address'] = $receiverAddress;
        $ship['receiver'] = $receiver;
        $neighbour = [];
        if (isset($post['package_additional']['deliveryToNeighbour']) && $post['package_additional']['deliveryToNeighbour'] == 'true') {
            if (isset($post['package_additional']['neighbour_name'])) {
                $neighbour['name'] = $post['package_additional']['neighbour_name'];
            }

            if (isset($post['package_additional']['neighbour_postcode'])) {
                $neighbour['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['package_additional']['neighbour_postcode']);
            }

            if (isset($post['package_additional']['neighbour_city'])) {
                $neighbour['city'] = $post['package_additional']['neighbour_city'];
            }

            if (isset($post['package_additional']['neighbour_street'])) {
                $neighbour['street'] = $post['package_additional']['neighbour_street'];
            }

            if (isset($post['package_additional']['neighbour_houseNumber'])) {
                $neighbour['houseNumber'] = $post['package_additional']['neighbour_houseNumber'];
            }

            if (isset($post['package_additional']['neighbour_apartmentNumber'])) {
                $neighbour['apartmentNumber'] = $post['package_additional']['neighbour_apartmentNumber'];
            }

            if (isset($post['package_additional']['neighbour_phoneNumber'])) {
                $neighbour['contactPhone'] = $post['package_additional']['neighbour_phoneNumber'];
            }

            if (isset($post['package_additional']['neighbour_emailAddress'])) {
                $neighbour['contactEmail'] = $post['package_additional']['neighbour_emailAddress'];
            }
        }

        $ship['neighbour'] = $neighbour;
        $shipment['ship'] = $ship;

        $pieceList = [];
        if (isset($post['package'])&& is_array($post['package']) && count($post['package']) > 0) {
            $_package = [];
            if (isset($post['package']['package_type'])) {
                if ($post['package']['package_type'] == 'ENVELOPE') {
                    $_package['type'] = $post['package']['package_type'];
                    if (isset($post['package']['quantity'])) {
                        $_package['quantity'] = $post['package']['quantity'];
                        $_package['nonStandard'] = false;
                    }
                } else {
                    $_package['type'] = $post['package']['package_type'];
                    if (isset($post['package']['weight'])) {
                        $_package['weight'] = $post['package']['weight'];
                    }
                    if (isset($post['package']['width'])) {
                        $_package['width'] = $post['package']['width'];
                    }
                    if (isset($post['package']['height'])) {
                        $_package['height'] = $post['package']['height'];
                    }
                    if (isset($post['package']['length'])) {
                        $_package['length'] = $post['package']['length'];
                    }
                    if (isset($post['package']['quantity'])) {
                        $_package['quantity'] = $post['package']['quantity'];
                    }
                    if (isset($post['package']['nonStandard'])) {
                        $_package['nonStandard'] = filter_var($post['package']['nonStandard'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
            if (count($_package) > 0) {
                $pieceList[] = $_package;
            }
        }

        $shipment['pieceList'] = $pieceList;
        return $shipment;
    }

    /**
     * @param $post
     * @param $magentoData
     * @return array
     * @throws Exception
     */
    public function buildReturnShipmentRequest($post, $magentoData): array
    {
        $shipment = $this->buildShipmentRequest($post, $magentoData);

        $returnShipmentParams['shipment'] = [
            'content' => $shipment['content'],
            'comment' => '',
            'reference' => $shipment['reference']
        ];

        $returnShipmentParams['shipment']['shipmentInfo'] = [
            'serviceType' => $this->getReturnServiceType($post),
            'bookCourier' => false,
            'billing' => [
                'shippingPaymentType' => self::RETURN_SHIPPING_PAYMENT_TYPE,
                'billingAccountNumber' => $this->returnShipmentConfig->getBillingAccountNumber(),
                'paymentType' => self::RETURN_PAYMENT_TYPE,
                'costsCenter' => $shipment['shipmentInfo']['billing']['costsCenter']
            ],
            'shipmentTime' => [
                'labelExpDate' => $this->getLabelExpirationDate()->format('Y-m-d')
            ],
            'labelType' => $shipment['shipmentInfo']['labelType'],
        ];

        $returnShipperCountry = $this->getReturnShipperCountry($post);
        $returnShipmentParams['shipment']['ship'] = $this->getReturnShipperAndReceiverSection($shipment, $returnShipperCountry);

        $returnShipmentParams['shipment']['pieceList'] = $this->convertPieceListForReturn($shipment['pieceList']);

        return $returnShipmentParams;
    }

    /**
     * @param array $post
     * @return string
     */
    protected function getReturnServiceType(array $post): string
    {
        if (
            $post['package_product_type']['product_type'] == 'EK' &&
            !empty($post['package_product_type']['ek_country']) &&
            $post['package_product_type']['ek_country'] != 'PL'
        ) {
            $serviceType = ReturnServiceType::RETURN_SERVICE_TYPE_CONNECT;
        } else {
            $serviceType = ReturnServiceType::RETURN_SERVICE_TYPE_DOMESTIC;
        }
        return $serviceType;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    protected function getLabelExpirationDate(): DateTime
    {
        $now = new DateTime('now');
        return $now->add(new DateInterval('P' . $this->returnShipmentConfig->getLabelExpDays() . 'D'));
    }

    /**
     * @param array $shipment
     * @param $returnShipperCountry
     * @return array|array[]
     */
    protected function getReturnShipperAndReceiverSection(array $shipment, $returnShipperCountry): array
    {
        if ($this->returnShipmentConfig->shouldUsePrimarySenderAsReturnReceiver()) {
            return [
                'shipper' => [
                    'preaviso' => $shipment['ship']['receiver']['preaviso'],
                    'contact' => $shipment['ship']['receiver']['contact'],
                    'address' => array_merge($shipment['ship']['receiver']['address'], ['country' => $returnShipperCountry]),
                ],
                'receiver' => [
                    'preaviso' => $shipment['ship']['shipper']['preaviso'],
                    'contact' => $shipment['ship']['shipper']['contact'],
                    'address' => array_merge($shipment['ship']['shipper']['address'], ['country' => 'PL']),
                ],
            ];
        } else {
            $data = [
                'shipper' => [
                    'preaviso' => $shipment['ship']['receiver']['preaviso'],
                    'contact' => $shipment['ship']['receiver']['contact'],
                    'address' => $shipment['ship']['receiver']['address'],
                ],
                'receiver' => [
                    'preaviso' => [
                        'personName' => $this->returnShipmentConfig->getPreavisoPersonName(),
                        'phoneNumber' => $this->returnShipmentConfig->getPreavisoPhoneNumber(),
                        'emailAddress' => $this->returnShipmentConfig->getPreavisoEmailAddress(),
                    ],
                    'contact' => [
                        'personName' => $this->returnShipmentConfig->getContactPersonName(),
                        'phoneNumber' => $this->returnShipmentConfig->getContactPhoneNumber(),
                        'emailAddress' => $this->returnShipmentConfig->getContactEmailAddress(),
                    ],
                    'address' => [
                        'country' => 'PL',
                        'name' => $this->returnShipmentConfig->getReceiverName(),
                        'postalCode' => $this->shipmentHelper->cleanPostalCode($this->returnShipmentConfig->getReceiverPostCode()),
                        'city' => $this->returnShipmentConfig->getReceiverCity(),
                        'street' => $this->returnShipmentConfig->getReceiverStreet(),
                        'houseNumber' => $this->returnShipmentConfig->getReceiverHouseNumber(),
                        'apartmentNumber' => $this->returnShipmentConfig->getReceiverApartmentNumber(),
                        'isPackstation' => 'false',
                        'isPostfiliale' => 'false',
                    ],
                ],
            ];
            if (isset($shipment['ship']['receiver']['postnummer'])) {
                $data['receiver']['address']['postnummer'] = $this->returnShipmentConfig->getReceiverPostNumber();
            }

            return $data;
        }

    }

    /**
     * @param ApiServiceType $serviceType
     * @return string
     */
    protected function getWsdlUrlFromServiceType(ApiServiceType $serviceType): string
    {
        switch ($serviceType){
            case ApiServiceType::MAGENTO_API():
                return ($this->getConfigApiType()) ? self::DHL_MAGENTOAPI : self::DHL_PRODUCTION_MAGENTOAPI;
            case ApiServiceType::SERVICE_POINT_API():
                return ($this->getConfigApiType()) ? self::DHL_SERVICEPOINTAPI : self::DHL_PRODUCTION_SERVICEPOINTAPI;
            case ApiServiceType::WEB_API2():
            default:
                return ($this->getConfigApiType()) ? self::DHL_WEBAPI2 : self::DHL_PRODUCTION_WEBAPI2;
        }
    }

    /**
     * @return bool
     */
    protected function getConfigApiType(): bool
    {
        return ($this->scopeConfig->getValue(self::DHL_API_PRODUCTION_TEST_TYPE) == '1') ? true : false;
    }

    /**
     * @param array $post
     * @return bool
     */
    public function shouldUseServicePointApi(array $post): bool
    {
        if (isset($this->useServicePointApi)) {
            return $this->useServicePointApi;
        }
        return (isset($post['package_additional']['deliveryToLM']) &&
            $post['package_additional']['deliveryToLM'] === "true" &&
            $this->isInternationalShipment($post) == false
        );
    }

    /**
     * @param array $post
     * @return bool
     */
    public function isInternationalServicePoint(array $post): bool
    {
        return (isset($post['package_additional']['deliveryToLM']) &&
            $post['package_additional']['deliveryToLM'] === "true" &&
            $this->isInternationalShipment($post) == true
        );
    }

    /**
     * @param $post
     * @param $magentoDHLConfig
     * @return array
     */
    public function buildUniversalShipmentRequest($post, $magentoDHLConfig)
    {
        if (!isset($this->useServicePointApi)) {
            $this->useServicePointApi = $this->shouldUseServicePointApi($post);
        }
        $this->insertParcelDataIntoReceiverAddress($post);
        $request = [];
        $auth = $this->dhlConfig->getApiAuthorizationArray($this->useServicePointApi);
        $shipper = $this->buildShipperData($post);
        $receiver = $this->buildReceiverData($post);
        $neighbour = $this->buildNeighbourData($post);
        $pieceList = $this->buildPieceList($post);
        $content = !empty($post['package']['content']) ? $post['package']['content'] : '';
        $reference = !empty($post['package_additional']['reference']) ? $post['package_additional']['reference'] : '';
        $comment = !empty($post['package_additional']['comment']) ? $post['package_additional']['comment'] : '';

        $shipmentInfo = $this->buildShipmentInfo($post, $magentoDHLConfig);

        $ship = array(
            'shipper' => $shipper,
            'receiver' => $receiver,
            'neighbour' => $neighbour
        );

        if ($this->useServicePointApi) {
            $shipmentInfo['shipmentDate'] = $shipmentInfo['shipmentTime']['shipmentDate'];
            $shipmentInfo['shipmentStartHour'] = $shipmentInfo['shipmentTime']['shipmentStartHour'];
            $shipmentInfo['shipmentEndHour'] = $shipmentInfo['shipmentTime']['shipmentEndHour'];
            $shipmentInfo['serviceType'] = self::SERVICE_POINT_SERVICE_TYPE;
            unset($shipmentInfo['shipmentTime']);
            unset($ship['neighbour']);
            $ship['servicePointAccountNumber'] = $post['package_additional']['lm_sap'];
            $ship['shipper']['address']['postcode'] = $ship['shipper']['address']['postalCode'];
            $ship['receiver']['address']['postcode'] = $ship['shipper']['address']['postalCode'];
            unset($ship['shipper']['address']['postalCode']);
            unset($ship['receiver']['address']['postalCode']);
            $request['shipment'] = array(
                'shipmentData' => array(
                    'ship' => $ship,
                    'shipmentInfo' => $shipmentInfo,
                    'pieceList' => $pieceList,
                    'content' => $content,
                    'reference' => $reference,
                    'comment' => $comment
                ),
                'authData' => $auth['authData']
            );
        } else {
            $request['authData'] = $auth['authData'];
            $request['shipment'] = array(
                'shipmentInfo' => $shipmentInfo,
                'content' => $content,
                'reference' => $reference,
                'comment' => $comment,
                'ship' => $ship,
                'pieceList' => $pieceList,
            );
        }

        return $request;
    }

    /**
     * @param array $post
     * @return array
     */
    protected function buildShipperData(array $post): array
    {
        $shipper = [];
        $shipperPreaviso = [];
        $shipperContact = [];
        if (isset($post['sender']['personName'])) {
            $shipperPreaviso['personName'] = $post['sender']['personName'];
            $shipperContact['personName'] = $post['sender']['personName'];

        }
        if (isset($post['sender']['phoneNumber'])) {
            $shipperPreaviso['phoneNumber'] = $this->shipmentHelper->clearPhone($post['sender']['phoneNumber']);
            $shipperContact['phoneNumber'] = $this->shipmentHelper->clearPhone($post['sender']['phoneNumber']);

        }
        if (isset($post['sender']['emailAddress'])) {
            $shipperPreaviso['emailAddress'] = $post['sender']['emailAddress'];
            $shipperContact['emailAddress'] = $post['sender']['emailAddress'];
        }
        $shipper['preaviso'] = $shipperPreaviso;
        $shipper['contact'] = $shipperContact;
        $shipperAddress = [];
        $shipperAddress['country'] = 'PL';
        if (isset($post['payer']['payerType'])) {
            $shipperAddress['name'] = $post['sender']['personName'];
        }
        if (isset($post['sender']['postcode'])) {
            $shipperAddress['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['sender']['postcode']);
        }
        if (isset($post['sender']['city'])) {
            $shipperAddress['city'] = $post['sender']['city'];
        }

        if (isset($post['sender']['street'])) {
            $shipperAddress['street'] = $post['sender']['street'];
        }
        if (isset($post['sender']['houseNumber'])) {
            $shipperAddress['houseNumber'] = $post['sender']['houseNumber'];
        }
        if (isset($post['sender']['apartmentNumber'])) {
            $shipperAddress['apartmentNumber'] = $post['sender']['apartmentNumber'];
        }
        $shipper['address'] = $shipperAddress;

        return $shipper;
    }

    /**
     * @param $post
     * @return array
     */
    protected function buildReceiverData($post): array
    {
        $receiver = [];
        $receiverPreaviso = [];
        $receiverContact = [];

        if (isset($post['recipient']['personName'])) {
            $receiverPreaviso['personName'] = $post['recipient']['personName'];
            $receiverPreaviso['name'] = $post['recipient']['personName'];
            $receiverContact['personName'] = $post['recipient']['personName'];
            $receiverContact['name'] = $post['recipient']['personName'];
        }
        if (isset($post['recipient']['phoneNumber'])) {
            $receiverPreaviso['phoneNumber'] = $this->shipmentHelper->clearPhone($post['recipient']['phoneNumber']);
            $receiverContact['phoneNumber'] = $this->shipmentHelper->clearPhone($post['recipient']['phoneNumber']);

        }
        if (isset($post['recipient']['emailAddress'])) {
            $receiverPreaviso['emailAddress'] = $post['recipient']['emailAddress'];
            $receiverContact['emailAddress'] = $post['recipient']['emailAddress'];
        }
        $receiver['preaviso'] = $receiverPreaviso;
        $receiver['contact'] = $receiverContact;
        $receiverAddress = [];

        if (isset($post['recipient']['personName'])) {
            $receiverAddress['name'] = $post['recipient']['personName'];
        }
        if (isset($post['recipient']['postcode'])) {
            $receiverAddress['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['recipient']['postcode']);
        }
        if (isset($post['recipient']['city'])) {
            $receiverAddress['city'] = $post['recipient']['city'];
        }
        if (isset($post['recipient']['street'])) {
            $receiverAddress['street'] = $post['recipient']['street'];
            if (is_array($post['recipient']['street'])) {
                $receiverAddress['street'] = $post['recipient']['street'][0];
            }
        }
        if (isset($post['recipient']['houseNumber'])) {
            $receiverAddress['houseNumber'] = $post['recipient']['houseNumber'];
        }
        if (isset($post['recipient']['apartmentNumber'])) {
            $receiverAddress['apartmentNumber'] = $post['recipient']['apartmentNumber'];
        }

        if (isset($post['recipient']['addressType'])) {
            $type = 'C';
            if($post['recipient']['addressType'] == 'true') {
                $type = 'B';
            }
            $receiverAddress['addressType'] = $type;
        }

        if ($this->isInternationalShipment($post)) {
            if (isset($post['package_product_type']['ek_country'])) {
                $receiverAddress['country'] = $post['package_product_type']['ek_country'];
            }
            if (isset($post['package_product_type']['ek_packstation']) && !empty($post['package_product_type']['ek_packstation'])) {
                $receiverAddress['houseNumber'] = $post['package_product_type']['ek_packstation'];
                $receiverAddress['isPackstation'] = true;
            } else {
                $receiverAddress['isPackstation'] = false;
            }
            if (isset($post['package_product_type']['ek_postfiliale']) && !empty($post['package_product_type']['ek_postfiliale'])) {
                $receiverAddress['houseNumber'] = $post['package_product_type']['ek_postfiliale'];
                $receiverAddress['isPostfiliale'] = true;
            } else {
                $receiverAddress['isPostfiliale'] = false;
            }
            if (isset($post['package_product_type']['ek_postnummer'])) {
                $receiverAddress['postnummer'] = $post['package_product_type']['ek_postnummer'];
            }
        } else {
            $receiverAddress['country'] = 'PL';
        }
        $receiver['address'] = $receiverAddress;

        return $receiver;
    }

    /**
     * @param array $post
     * @return bool
     */
    protected function isInternationalShipment(array $post): bool
    {
        return isset($post['package_product_type']['product_type']) &&
            $post['package_product_type']['product_type'] == self::INTERNATIONAL_PACKAGE_CODE;
    }

    /**
     * @param array $post
     * @return array
     */
    protected function buildNeighbourData(array $post): array
    {
        $neighbour = [];
        if (isset($post['package_additional']['deliveryToNeighbour']) &&
            ($post['package_additional']['deliveryToNeighbour'] == '1' || $post['package_additional']['deliveryToNeighbour'] == 'true')
        ) {
            if (isset($post['package_additional']['neighbour_name'])) {
                $neighbour['name'] = $post['package_additional']['neighbour_name'];
            }
            if (isset($post['package_additional']['neighbour_postcode'])) {
                $neighbour['postalCode'] = $this->shipmentHelper->cleanPostalCode($post['package_additional']['neighbour_postcode']);
            }
            if (isset($post['package_additional']['neighbour_city'])) {
                $neighbour['city'] = $post['package_additional']['neighbour_city'];
            }
            if (isset($post['package_additional']['neighbour_street'])) {
                $neighbour['street'] = $post['package_additional']['neighbour_street'];
            }
            if (isset($post['package_additional']['neighbour_houseNumber'])) {
                $neighbour['houseNumber'] = $post['package_additional']['neighbour_houseNumber'];
            }
            if (isset($post['package_additional']['neighbour_apartmentNumber'])) {
                $neighbour['apartmentNumber'] = $post['package_additional']['neighbour_apartmentNumber'];
            }
            if (isset($post['package_additional']['neighbour_phoneNumber'])) {
                $neighbour['contactPhone'] = $post['package_additional']['neighbour_phoneNumber'];
            }
            if (isset($post['package_additional']['neighbour_emailAddress'])) {
                $neighbour['contactEmail'] = $post['package_additional']['neighbour_emailAddress'];
            }
        }
        return $neighbour;
    }

    /**
     * @param $post
     * @return array
     */
    protected function buildPieceList($post)
    {
        $pieceList = [];
        $shouldUseServicePoint = $this->shouldUseServicePointApi($post);
        if (isset($post['package'])&& is_array($post['package']) && count($post['package']) > 0) {
            $_package = [];
            if (isset($post['package']['package_type'])) {
                if ($post['package']['package_type'] == 'ENVELOPE') {
                    $_package['type'] = $post['package']['package_type'];
                    if (isset($post['package']['quantity'])) {
                        $_package['quantity'] = $post['package']['quantity'];
                        $_package['nonStandard'] = false;
                    }
                } else {
                    $_package['type'] = $post['package']['package_type'];
                    if (isset($post['package']['weight'])) {
                        $_package['weight'] = $post['package']['weight'];
                    }
                    if (isset($post['package']['width'])) {
                        $_package['width'] = $post['package']['width'];
                    }
                    if (isset($post['package']['height'])) {
                        $_package['height'] = $post['package']['height'];
                    }
                    if (isset($post['package']['length'])) {
                        if ($shouldUseServicePoint) {
                            $_package['lenght'] = $post['package']['length'];
                        } else {
                            $_package['length'] = $post['package']['length'];
                        }
                    }
                    if (isset($post['package']['quantity'])) {
                        $_package['quantity'] = $post['package']['quantity'];
                    }

                    if (isset($post['package']['nonStandard'])) {
                        $_package['nonStandard'] = filter_var($post['package']['nonStandard'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
            if (count($_package) > 0) {
                $pieceList[] = $_package;
            }
        }

        return $pieceList;
    }

    /**
     * @param $post
     * @param $magentoData
     * @return array
     */
    private function buildShipmentInfo($post, $magentoData)
    {
        $shipmentInfo = [];
        $shipmentInfo['dropOffType'] = $magentoData['data']['dropOffType'];
        if($this->scopeConfig->getValue(self::REGULAR_PICKUP_CONFIG)) {
            $shipmentInfo['dropOffType'] = self::REGULAR_PICKUP;
        }

        if (isset($post['package_product_type']['product_type'])) {
            $shipmentInfo['serviceType'] = $post['package_product_type']['product_type'];
            if ($post['package_product_type']['product_type'] == 'AHM') {
                $shipmentInfo['serviceType'] = 'AH';
            }
        }

        $billing = [];
        if (isset($post['payer']['payerType'])) {
            if ($post['payer']['payerType'] == 'N') {
                $billing['shippingPaymentType'] = 'SHIPPER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            } elseif ($post['payer']['payerType'] == 'O') {
                $billing['shippingPaymentType'] = 'RECEIVER';
                $billing['paymentType'] = 'CASH';
            } elseif($post['payer']['payerType'] == 'T') {
                $billing['shippingPaymentType'] = 'USER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            }
        }
        if (isset($post['sender']['sap'])) {
            $billing['billingAccountNumber'] = $post['sender']['sap'];
        }
        if (isset($post['package_additional']['costsCenter'])) {
            $billing['costsCenter'] = $post['package_additional']['costsCenter'];
        }
        $shipmentInfo['billing'] = $billing;
        $shipmentInfo['labelType'] = $magentoData['data']['labelType'];

        $specialServices = [];

        if (isset($post['package_additional']['insurance']) && $post['package_additional']['insurance'] == 'true') {
            $item = [];
            $item['serviceType'] = 'UBEZP';
            if (isset($post['package_additional']['insuranceValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['package_additional']['insuranceValue']);
            }
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['collectOnDelivery']) && $post['package_additional']['collectOnDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'COD';
            if (isset($post['package_additional']['collectOnDeliveryValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['package_additional']['collectOnDeliveryValue']);
            }
            $item['collectOnDeliveryForm'] = 'BANK_TRANSFER';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['predeliveryInformation']) && $post['package_additional']['predeliveryInformation'] == 'true') {
            $item = [];
            $item['serviceType'] = 'PDI';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['returnOnDelivery']) && $post['package_additional']['returnOnDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'ROD';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['proofOfDelivery']) && $post['package_additional']['proofOfDelivery'] == 'true') {
            $item = [];
            $item['serviceType'] = 'POD';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['deliveryEvening']) && $post['package_additional']['deliveryEvening'] == 'true') {
            $item = [];
            $item['serviceType'] = '1722';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['deliveryOnSaturday']) && $post['package_additional']['deliveryOnSaturday'] == 'true') {
            $item = [];
            $item['serviceType'] = 'SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['pickupOnSaturday']) && $post['package_additional']['pickupOnSaturday'] == 'true') {
            $item = [];
            $item['serviceType'] = 'NAD_SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['selfCollect']) && $post['package_additional']['selfCollect'] == 'true') {
            $item = [];
            $item['serviceType'] = 'ODB';
            $specialServices[] = $item;
        }
        if (isset($post['package_additional']['deliveryToNeighbour']) && $post['package_additional']['deliveryToNeighbour'] == 'true') {
            $item = [];
            $item['serviceType'] = 'SAS';
            $specialServices[] = $item;
        }
        $shipmentInfo['specialServices'] = $specialServices;
        $shipmentTime = [];

        if (isset($post['package_shipment']['shipmentDate'])) {
            $shipmentTime['shipmentDate'] = $post['package_shipment']['shipmentDate'];
        }
        if (isset($post['package_shipment']['shipmentStartHour'])) {
            $shipmentTime['shipmentStartHour'] = $post['package_shipment']['shipmentStartHour'];
        }
        if (isset($post['package_shipment']['shipmentEndHour'])) {
            $shipmentTime['shipmentEndHour'] = $post['package_shipment']['shipmentEndHour'];
        }
        $shipmentInfo['shipmentTime'] = $shipmentTime;

        return $shipmentInfo;
    }

    /**
     * @param $post
     * @return string
     */
    protected function getReturnShipperCountry($post): string
    {
        return !empty($post['package_product_type']['ek_country']) ? $post['package_product_type']['ek_country'] : 'PL';
    }

    /**
     * @param array $pieceList
     * @return array
     */
    protected function convertPieceListForReturn(array $pieceList): array
    {
        return array_map(function($piece) {
            $piece['nonStandard'] = filter_var($piece['nonStandard'], FILTER_VALIDATE_BOOLEAN);
            return $piece;
        }, $pieceList);
    }

    /**
     * @param array $post
     */
    protected function insertParcelDataIntoReceiverAddress(array &$post)
    {
        if ($this->isInternationalServicePoint($post) && isset($post['parcelData'])) {
            $post['recipient']['city'] = $post['parcelData']['city'];
            $post['recipient']['postcode'] = $post['parcelData']['postcode'];
        }
    }
}
