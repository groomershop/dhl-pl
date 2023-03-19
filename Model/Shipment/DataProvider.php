<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Shipment;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;
use Magento\Sales\Model\OrderRepository;
use DHL\Dhl24pl\Helper\Api as ApiHelper;
use DHL\Dhl24pl\Model\Carrier\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use DHL\Dhl24pl\Helper\Shipment as ShipmentHelper;
use DHL\Dhl24pl\Helper\Data as DataHelper;
use DHL\Dhl24pl\Model\PackageFactory;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class DataProvider
 * @package DHL\Dhl24pl\Model\Shipment
 */
class DataProvider extends MagentoDataProvider
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ShipmentHelper
     */
    protected $shipmentHelper;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var PackageFactory
     */
    protected $packageFactory;
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
     * @param OrderRepository $orderRepository
     * @param ApiHelper $apiHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param ShipmentHelper $shipmentHelper
     * @param DataHelper $dataHelper
     * @param PackageFactory $packageFactory
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
        OrderRepository $orderRepository,
        ApiHelper $apiHelper,
        ScopeConfigInterface $scopeConfig,
        ShipmentHelper $shipmentHelper,
        DataHelper $dataHelper,
        PackageFactory $packageFactory,
        SenderFactory $senderFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->orderRepository = $orderRepository;
        $this->apiHelper = $apiHelper;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentHelper = $shipmentHelper;
        $this->dataHelper = $dataHelper;
        $this->packageFactory = $packageFactory;
        $this->senderFactory = $senderFactory;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $orderId = $this->request->getParam('order_id');

        if($this->request->getParam('recipient')) {
            $request = $this->request->getParams();
            $data[$orderId] = $this->request->getParams();
            $data[$orderId]['package_additional']['deliveryEvening'] = ($request['package_additional']['deliveryEvening'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['deliveryOnSaturday'] = ($request['package_additional']['deliveryOnSaturday'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['pickupOnSaturday'] = ($request['package_additional']['pickupOnSaturday'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['insurance'] = ($request['package_additional']['insurance'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['collectOnDelivery'] = ($request['package_additional']['collectOnDelivery'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['returnOnDelivery'] = ($request['package_additional']['returnOnDelivery'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['proofOfDelivery'] = ($request['package_additional']['proofOfDelivery'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['selfCollect'] = ($request['package_additional']['selfCollect'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['deliveryToNeighbour'] = ($request['package_additional']['deliveryToNeighbour'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['deliveryToLM'] = ($request['package_additional']['deliveryToLM'] == 'true') ? true : false;
            $data[$orderId]['package_additional']['predeliveryInformation'] = ($request['package_additional']['predeliveryInformation'] == 'true') ? true : false;
            $data[$orderId]['package']['nonStandard'] = ($request['package']['nonStandard'] == 'true') ? true : false;
            $data[$orderId]['recipient']['addressType'] = ($request['recipient']['addressType'] == 'true') ? true : false;

            return $data;
        }

        $order = $this->orderRepository->get($orderId);
        $data[$orderId]['order_id'] = $orderId;

        if ($storeInfo = $this->dataHelper->getStoreInformation()) {
            $data[$orderId]['payer']['payerType'] = 'N';
            $data[$orderId]['sender']['postcode'] = $storeInfo['postcode'];
            $data[$orderId]['sender']['sap'] = $storeInfo['sap'];
            $data[$orderId]['sender']['city'] = $storeInfo['city'];
            if(!empty($storeInfo['street_line1'])) {
                $storeStreet = $this->dataHelper->pregStreet($storeInfo['street_line1']);
                $data[$orderId]['sender']['street'] = $storeStreet['street'];
                $data[$orderId]['sender']['houseNumber'] = $storeStreet['houseNumber'];
                $data[$orderId]['sender']['apartmentNumber'] = $storeStreet['apartmentNumber'];
            }
            $data[$orderId]['sender']['name'] = $storeInfo['contact_person'];
            $data[$orderId]['sender']['personName'] = $storeInfo['contact_person'];
            $data[$orderId]['sender']['phoneNumber'] = $storeInfo['phone'];
            $data[$orderId]['sender']['emailAddress'] = $storeInfo['email'];
        }

        $data[$orderId]['recipient']['postcode'] = $order->getShippingAddress()->getPostcode();
        $data[$orderId]['recipient']['city'] = $order->getShippingAddress()->getCity();
        $data[$orderId]['recipient']['recipient_country'] = $order->getShippingAddress()->getCountryId();

        $street = $order->getShippingAddress()->getStreet();
        if (isset($street[0])) {
            $data[$orderId]['recipient']['street'] = $street[0];
            $streetFull = $street[0];
            if (isset($street[1])) {
                $streetFull .=  ' ' . $street[1];
            }
            if (isset($street[2])) {
                $streetFull .=  ' ' . $street[2];
            }
            if ($streetArray = $this->shipmentHelper->preg($streetFull)) {
                if (isset($streetArray['street'])) {
                    $data[$orderId]['recipient']['street'] = $streetArray['street'];
                }
                if (isset($streetArray['houseNumber'])) {
                    $data[$orderId]['recipient']['houseNumber'] = $streetArray['houseNumber'];
                }
                if (isset($streetArray['apartmentNumber'])) {
                    $data[$orderId]['recipient']['apartmentNumber'] = $streetArray['apartmentNumber'];
                }
            } else {
                $data[$orderId]['recipient']['houseNumber'] = $street[1] ?? '';
                $data[$orderId]['recipient']['apartmentNumber'] = $street[2] ?? '';
            }
        }

        $data[$orderId]['recipient']['phoneNumber'] = $order->getShippingAddress()->getTelephone();
        $data[$orderId]['recipient']['emailAddress'] = $order->getShippingAddress()->getEmail();
        $data[$orderId]['recipient']['personName'] = $order->getShippingAddress()->getName();


        if($order->getShippingMethod() == Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_DHL12) {
            $data[$orderId]['package_product_type']['product_type'] = '12';
        }

        if($order->getShippingMethod() == Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_DHL09) {
            $data[$orderId]['package_product_type']['product_type'] = '09';
        }

        if($order->getShippingMethod() == Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_DHL_PREMIUM) {
            $data[$orderId]['package_product_type']['product_type'] = 'PR';
        }

        if($order->getShippingMethod() == Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_DHL_MAX) {
            $data[$orderId]['package_product_type']['product_type'] = 'AHM';
        }

        if($order->getShippingMethod() == Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_EVENING) {
            $data[$orderId]['package_additional']['deliveryEvening'] = true;
        }

        if($order->getShippingAddress()->getCountryId() !== 'PL') {
            $data[$orderId]['package_product_type']['product_type'] = 'EK';
            $data[$orderId]['package_product_type']['ek_country'] = $order->getShippingAddress()->getCountryId();
        }

        if($order->getDhlplParcelshop()) {
            $parcel = json_decode($order->getDhlplParcelshop(), true);
            $data[$orderId]['package_additional']['deliveryToLM'] = true;
            $data[$orderId]['package_additional']['lm_sap'] = $parcel['sap'];
        }

        $variant = $this->scopeConfig->getValue('dhl24pl/cod/variant', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $courierCodeCod = Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_COURIER_COD;
        $parcelshopCodCode = Carrier::CARRIER_CODE . '_' . Carrier::CARRIER_PARCELSHOP_COD_CODE;

        if ($order->getShippingMethod() == $courierCodeCod || $order->getShippingMethod() == $parcelshopCodCode) {
            if ($variant == 'all') {
                $price = $order->getGrandTotal();
            } elseif ($variant == 'products') {
                $price = $order->getSubtotalInclTax();
            }

            if ($price !== false) {
                $data[$orderId]['package_additional']['insurance'] = true;
                $data[$orderId]['package_additional']['insuranceValue'] = number_format((float)$price, 2, '.', '');
                $data[$orderId]['package_additional']['collectOnDelivery'] = true;
                $data[$orderId]['package_additional']['collectOnDeliveryValue'] = number_format((float)$price, 2, '.', '');
            }
        }

        if($order->getDhlplNeighbor()) {
            $neighbor = json_decode($order->getDhlplNeighbor(),true);
            if(isset($neighbor['is_neighbours']) && $neighbor['is_neighbours'] == 'true') {
                $data[$orderId]['package_additional']['deliveryToNeighbour'] = true;
                $data[$orderId]['package_additional']['neighbour_name'] = $neighbor['neighbor_name'];
                $data[$orderId]['package_additional']['neighbour_postcode'] = $neighbor['neighbor_postcode'];
                $data[$orderId]['package_additional']['neighbour_city'] = $neighbor['neighbor_city'];
                $data[$orderId]['package_additional']['neighbour_street'] = $neighbor['neighbor_street'];
                $data[$orderId]['package_additional']['neighbour_houseNumber'] = $neighbor['neighbor_houseNumber'];
                $data[$orderId]['package_additional']['neighbour_apartmentNumber'] = $neighbor['neighbor_apartmentNumber'];
                $data[$orderId]['package_additional']['neighbour_phoneNumber'] = $neighbor['neighbor_phoneNumber'];
                $data[$orderId]['package_additional']['neighbour_emailAddress'] = $neighbor['neighbor_emailAddress'];
            }
        }

        $packageFactoryData = $this->packageFactory->create()->getCollection();
        if(count($packageFactoryData->getData()) > 0) {
            foreach ($packageFactoryData->getData() as $shipment) {
                if(isset($shipment['is_default']) && $shipment['is_default'] == true) {
                    $data[$orderId]['sender']['is_default'] = true;
                    $data[$orderId]['sender']['name'] = $shipment['sender'];
                    if($order->getShippingAddress()->getCountryId() == 'PL') {
                        $data[$orderId]['package_product_type']['product_type'] = $shipment['product_type'];
                    }
                    $data[$orderId]['payer']['payerType'] = $shipment['payer'];
                    $data[$orderId]['payer']['package_schema'] = $shipment['name'];
                    $data[$orderId]['package']['package_type'] = $shipment['package_type'];
                    $data[$orderId]['package']['width'] = $shipment['width'];
                    $data[$orderId]['package']['height'] = $shipment['height'];
                    $data[$orderId]['package']['length'] = $shipment['lenght'];
                    $data[$orderId]['package']['weight'] = $shipment['weight'];
                    $data[$orderId]['package']['quantity'] = $shipment['quantity'];
                    $data[$orderId]['package']['nonStandard'] = (bool)$shipment['non_standard'];
                    $data[$orderId]['package']['content'] = $shipment['content'];
                    $data[$orderId]['package_additional']['deliveryEvening'] = (bool)$shipment['delivery_evening'];
                    $data[$orderId]['package_additional']['deliveryOnSaturday'] = (bool)$shipment['delivery_on_saturday'];
                    $data[$orderId]['package_additional']['pickupOnSaturday'] = (bool)$shipment['pickup_on_saturday'];
                    $data[$orderId]['package_additional']['returnOnDelivery'] = (bool)$shipment['return_on_delivery'];
                    $data[$orderId]['package_additional']['proofOfDelivery'] = (bool)$shipment['proof_of_delivery'];
                    $data[$orderId]['package_additional']['selfCollect'] = (bool)$shipment['self_collect'];
                    $data[$orderId]['package_additional']['deliveryToNeighbour'] = (bool)$shipment['delivery_to_neighbour'];
                    $data[$orderId]['package_additional']['predeliveryInformation'] = (bool)$shipment['predelivery_information'];
                    $data[$orderId]['package_additional']['costsCenter'] = $shipment['costs_center'];
                    $data[$orderId]['package_additional']['comment'] = $shipment['comment'];
                    if ($order->getShippingMethod() !== $courierCodeCod && $order->getShippingMethod() !== $parcelshopCodCode) {
                        $data[$orderId]['package_additional']['insurance'] = (bool)$shipment['insurance'];
                        $data[$orderId]['package_additional']['insuranceValue'] = $shipment['insurance_price'];
                    }

                }
            }
        }

        if(isset($data[$orderId]['sender']['is_default'])) {
            $senderFactoryData = $this->senderFactory->create()->getCollection();
            if(count($senderFactoryData->getData()) > 0) {
                foreach ($senderFactoryData->getData() as $sender) {
                    if($data[$orderId]['sender']['name'] == $sender['name']) {
                        $data[$orderId]['sender']['postcode'] = $sender['postcode'];
                        $data[$orderId]['sender']['sap'] = $sender['sap'];
                        $data[$orderId]['sender']['city'] = $sender['city'];
                        $data[$orderId]['sender']['street'] = $sender['street'];
                        $data[$orderId]['sender']['houseNumber'] = $sender['street_number'];
                        $data[$orderId]['sender']['apartmentNumber'] = $sender['house_number'];
                        $data[$orderId]['sender']['name'] = $sender['name'];
                        $data[$orderId]['sender']['personName'] = $sender['name'];
                        $data[$orderId]['sender']['phoneNumber'] = $sender['phone'];
                        $data[$orderId]['sender']['emailAddress'] = $sender['email'];
                    }
                }
            }
        }

        $data[$orderId]['sender']['url'] = $this->apiHelper->getBackendUrl('dhl/shipment/sender');
        $data[$orderId]['payer']['packageSchemaUrl'] = $this->apiHelper->getBackendUrl('dhl/shipment/shipment');
        $data[$orderId]['sender']['sender_country'] = 'PL';

        return $data;
    }
}
