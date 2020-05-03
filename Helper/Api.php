<?php namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Action\Action;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DHL_MAGENTOAPI = 'https://dhl24.com.pl/magentoapi';
    const DHL_WEBAPI2 = 'https://dhl24.com.pl/webapi2';
    const DHL_SERVICEPOINTAPI = 'https://dhl24.com.pl/servicepoint';

    const MAP_URL = 'https://parcelshop.dhl.pl/mapa';

    protected $_scopeConfig;

    protected $_objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    /** Metoda przygotowuje dane wejściowe oraz tworzy przesylke za pomoca metody api webapi2
     * @param $post - dane z formularza
     * @param $magentoData - dane z panelu magento z dhl
     * @param $magentoSender - wybrany nadawca
     * @return mixed
     * @throws \Exception
     */
    public function createWebapiShipment($post, $magentoData, $magentoSender) {
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $shipment = array();
        $shipmentInfo = array();
        $shipmentInfo['dropOffType'] = $magentoData['dropOffType'];
        if (isset($post['product'])) {
            $shipmentInfo['serviceType'] = $post['product'];
        }
        $billing = array();
        if (isset($post['payer'])) {
            if ($post['payer'] == 'N') {
                $billing['shippingPaymentType'] = 'SHIPPER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            } elseif ($post['payer'] == 'O') {
                $billing['shippingPaymentType'] = 'RECEIVER';
                $billing['paymentType'] = 'CASH';
            } elseif($post['payer'] == 'T') {
                $billing['shippingPaymentType'] = 'USER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            }
        }
        if (isset($magentoSender['sap'])) {
            $billing['billingAccountNumber'] = $magentoSender['sap'];
        }
        if (isset($post['costsCenter'])) {
            $billing['costsCenter'] = $post['costsCenter'];
        }
        $shipmentInfo['billing'] = $billing;
        $shipmentInfo['labelType'] = $magentoData['labelType'];

        $specialServices = array();
        if (isset($post['insurance']) && $post['insurance'] == '1') {
            $item = array();
            $item['serviceType'] = 'UBEZP';
            if (isset($post['insuranceValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['insuranceValue']);
            }
            $specialServices[] = $item;
        }
        if (isset($post['collectOnDelivery']) && $post['collectOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'COD';
            if (isset($post['collectOnDeliveryValue'])) {
                $item['serviceValue'] = str_replace(',', '.', $post['collectOnDeliveryValue']);
            }
            $item['collectOnDeliveryForm'] = 'BANK_TRANSFER';
            $specialServices[] = $item;
        }
        if (isset($post['predeliveryInformation']) && $post['predeliveryInformation'] == '1') {
            $item = array();
            $item['serviceType'] = 'PDI';
            $specialServices[] = $item;
        }
        if (isset($post['returnOnDelivery']) && $post['returnOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'ROD';
            $specialServices[] = $item;
        }
        if (isset($post['proofOfDelivery']) && $post['proofOfDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'POD';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryEvening']) && $post['deliveryEvening'] == '1') {
            $item = array();
            $item['serviceType'] = '1722';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryOnSaturday']) && $post['deliveryOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['pickupOnSaturday']) && $post['pickupOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'NAD_SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['selfCollect']) && $post['selfCollect'] == '1') {
            $item = array();
            $item['serviceType'] = 'ODB';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryToNeighbour']) && $post['deliveryToNeighbour'] == '1') {
            $item = array();
            $item['serviceType'] = 'SAS';
            $specialServices[] = $item;
        }
        $shipmentInfo['specialServices'] = $specialServices;
        $shipmentTime = array();
        if (isset($post['shipmentDate'])) {
            $shipmentTime['shipmentDate'] = $post['shipmentDate'];
        }
        if (isset($post['shipmentStartHour'])) {
            $shipmentTime['shipmentStartHour'] = $post['shipmentStartHour'];
        }
        if (isset($post['shipmentEndHour'])) {
            $shipmentTime['shipmentEndHour'] = $post['shipmentEndHour'];
        }
        $shipmentInfo['shipmentTime'] = $shipmentTime;
        $shipment['shipmentInfo'] = $shipmentInfo;
        if (isset($post['content'])) {
            $shipment['content'] = $post['content'];
        }
        if (isset($post['comment'])) {
            $shipment['comment'] = $post['comment'];
        }
        if (isset($post['reference'])) {
            $shipment['reference'] = $post['reference'];
        }
        if (isset($post['reference'])) {
            $shipment['reference'] = $post['reference'];
        }

        $ship = array();
        $shipper = array();
        $shipperPreaviso = array();
        $shipperContact = array();
        if (isset($post['personName'])) {
            $shipperPreaviso['personName'] = $post['personName'];
            $shipperContact['personName'] = $post['personName'];

        }
        if (isset($post['phoneNumber'])) {
            $shipperPreaviso['phoneNumber'] = $shipmentHelper->clearPhone($post['phoneNumber']);
            $shipperContact['phoneNumber'] = $shipmentHelper->clearPhone($post['phoneNumber']);

        }
        if (isset($post['emailAddress'])) {
            $shipperPreaviso['emailAddress'] = $post['emailAddress'];
            $shipperContact['emailAddress'] = $post['emailAddress'];
        }
        $shipper['preaviso'] = $shipperPreaviso;
        $shipper['contact'] = $shipperContact;
        $shipperAddress = array();
        if (isset($post['payer'])) {
            if (($post['payer'] == 'N' || $post['payer'] == 'O')) {
                if (isset($post['name'])) {
                    $shipperAddress['name'] = $post['name'];
                }
            } else {
                if (isset($post['name2'])) {
                    $shipperAddress['name'] = $post['name2'];
                }
            }
        }
        if (isset($post['postcode'])) {
            $shipperAddress['postalCode'] = $shipmentHelper->cleanPostalCode($post['postcode']);
        }
        if (isset($post['city'])) {
            $shipperAddress['city'] = $post['city'];
        }
        if (isset($post['street'])) {
            $shipperAddress['street'] = $post['street'];
        }
        if (isset($post['houseNumber'])) {
            $shipperAddress['houseNumber'] = $post['houseNumber'];
        }
        if (isset($post['apartmentNumber'])) {
            $shipperAddress['apartmentNumber'] = $post['apartmentNumber'];
        }
        $shipper['address'] = $shipperAddress;
        $ship['shipper'] = $shipper;
        $receiver = array();
        $receiverPreaviso = array();
        $receiverContact = array();
        if (isset($post['rec_personName'])) {
            $receiverPreaviso['personName'] = $post['rec_personName'];
            $receiverContact['personName'] = $post['rec_personName'];

        }
        if (isset($post['rec_phoneNumber'])) {
            $receiverPreaviso['phoneNumber'] = $shipmentHelper->clearPhone($post['rec_phoneNumber']);
            $receiverContact['phoneNumber'] = $shipmentHelper->clearPhone($post['rec_phoneNumber']);

        }
        if (isset($post['rec_emailAddress'])) {
            $receiverPreaviso['emailAddress'] = $post['rec_emailAddress'];
            $receiverContact['emailAddress'] = $post['rec_emailAddress'];
        }
        $receiver['preaviso'] = $receiverPreaviso;
        $receiver['contact'] = $receiverContact;
        $receiverAddress = array();

        if (isset($post['rec_name'])) {
            $receiverAddress['name'] = $post['rec_name'];
        }
        if (isset($post['rec_postcode'])) {
            $receiverAddress['postalCode'] = $shipmentHelper->cleanPostalCode($post['rec_postcode']);
        }
        if (isset($post['rec_city'])) {
            $receiverAddress['city'] = $post['rec_city'];
        }
        if (isset($post['rec_street'])) {
            $receiverAddress['street'] = $post['rec_street'];
        }
        if (isset($post['rec_houseNumber'])) {
            $receiverAddress['houseNumber'] = $post['rec_houseNumber'];
        }
        if (isset($post['rec_apartmentNumber'])) {
            $receiverAddress['apartmentNumber'] = $post['rec_apartmentNumber'];
        }
        //dodanie danych dla EK
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
        $neighbour = array();
        if (isset($post['deliveryToNeighbour']) && $post['deliveryToNeighbour'] == '1') {
            if (isset($post['neighbour_name'])) {
                $neighbour['name'] = $post['neighbour_name'];
            }
            if (isset($post['neighbour_postcode'])) {
                $neighbour['postalCode'] = $shipmentHelper->cleanPostalCode($post['neighbour_postcode']);
            }
            if (isset($post['neighbour_city'])) {
                $neighbour['city'] = $post['neighbour_city'];
            }
            if (isset($post['neighbour_street'])) {
                $neighbour['street'] = $post['neighbour_street'];
            }
            if (isset($post['neighbour_houseNumber'])) {
                $neighbour['houseNumber'] = $post['neighbour_houseNumber'];
            }
            if (isset($post['neighbour_apartmentNumber'])) {
                $neighbour['apartmentNumber'] = $post['neighbour_apartmentNumber'];
            }
            if (isset($post['neighbour_phoneNumber'])) {
                $neighbour['contactPhone'] = $post['neighbour_phoneNumber'];
            }
            if (isset($post['neighbour_emailAddress'])) {
                $neighbour['contactEmail'] = $post['neighbour_emailAddress'];
            }
        }
        $ship['neighbour'] = $neighbour;
        $shipment['ship'] = $ship;

        $pieceList = array();
        if (isset($post['Package'])&& is_array($post['Package']) && count($post['Package']) > 0) {
            foreach ($post['Package'] as $package) {
                $_package = array();
                if (isset($package['packageType'])) {
                    if ($package['packageType'] == 'ENVELOPE') {
                        $_package['type'] = $package['packageType'];
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                    } else {
                        $_package['type'] = $package['packageType'];
                        if (isset($package['weight'])) {
                            $_package['weight'] = $package['weight'];
                        }
                        if (isset($package['width'])) {
                            $_package['width'] = $package['width'];
                        }
                        if (isset($package['height'])) {
                            $_package['height'] = $package['height'];
                        }
                        if (isset($package['length'])) {
                            $_package['length'] = $package['length'];
                        }
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                        if (isset($package['nonStandard'])) {
                            $_package['nonStandard'] = $package['nonStandard'];
                        }
                    }
                }
                if (count($_package) > 0) {
                    $pieceList[] = $_package;
                }
            }
        }
        $shipment['pieceList'] = $pieceList;

        $login = $this->_scopeConfig->getValue('dhl24pl/webapi/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/webapi/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            "authData" => array('username' => $login, 'password' => $pass),
        );
        $params['shipment'] = $shipment;

        $context = stream_context_create(array('http' => array('header' => 'DHLApiOrgin: MAGENTO')));
        $_instance = new \SoapClient(
            self::DHL_WEBAPI2,
            array( 'keep-alive' => true, 'connection_timeout' => 3, 'stream_context' => $context )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $result = $_instance->createShipment( $params );

        return $result;
    }

    /** Metoda tworzy tablice parametrow i tworzy przesylke za pomoca api servicepoint
     * @param $post - dane z formularza
     * @param $magentoData - dane pobrane z dhl
     * @param $magentoSender - dane nadawcy z panelu Magento
     * @return mixed
     * @throws \Exception
     */
    public function createServicePointShipment($post, $magentoData, $magentoSender) {
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        $shipment = array();
        $shipmentData = array();

        $ship = array();
        //START::DANE NADAWCY
        $shipper = array();
        $shipperPreaviso = array();
        $shipperContact = array();
        if (isset($post['personName'])) {
            $shipperPreaviso['personName'] = $post['personName'];
            $shipperContact['personName'] = $post['personName'];

        }
        if (isset($post['phoneNumber'])) {
            $shipperPreaviso['phoneNumber'] = $post['phoneNumber'];
            $shipperContact['phoneNumber'] = $post['phoneNumber'];

        }
        if (isset($post['emailAddress'])) {
            $shipperPreaviso['emailAddress'] = $post['emailAddress'];
            $shipperContact['emailAddress'] = $post['emailAddress'];
        }
        $shipper['preaviso'] = $shipperPreaviso;
        $shipper['contact'] = $shipperContact;
        $shipperAddress = array();
        if (isset($post['payer'])) {
            if (($post['payer'] == 'N' || $post['payer'] == 'O')) {
                if (isset($post['name'])) {
                    $shipperAddress['name'] = $post['name'];
                }
            } else {
                if (isset($post['name2'])) {
                    $shipperAddress['name'] = $post['name2'];
                }
            }
        }
        if (isset($post['postcode'])) {
            $shipperAddress['postcode'] = $shipmentHelper->cleanPostalCode($post['postcode']);
        }
        if (isset($post['city'])) {
            $shipperAddress['city'] = $post['city'];
        }
        if (isset($post['street'])) {
            $shipperAddress['street'] = $post['street'];
        }
        if (isset($post['houseNumber'])) {
            $shipperAddress['houseNumber'] = $post['houseNumber'];
        }
        if (isset($post['apartmentNumber'])) {
            $shipperAddress['apartmentNumber'] = $post['apartmentNumber'];
        }
        $shipper['address'] = $shipperAddress;
        $ship['shipper'] = $shipper;
        //END::DANE NADAWCY
        //START::DANE ODBIORCY
        $receiver = array();
        $receiverPreaviso = array();
        $receiverContact = array();
        if (isset($post['rec_personName'])) {
            $receiverPreaviso['personName'] = $post['rec_personName'];
            $receiverContact['personName'] = $post['rec_personName'];

        }
        if (isset($post['rec_phoneNumber'])) {
            $receiverPreaviso['phoneNumber'] = $post['rec_phoneNumber'];
            $receiverContact['phoneNumber'] = $post['rec_phoneNumber'];

        }
        if (isset($post['rec_emailAddress'])) {
            $receiverPreaviso['emailAddress'] = $post['rec_emailAddress'];
            $receiverContact['emailAddress'] = $post['rec_emailAddress'];
        }
        $receiver['preaviso'] = $receiverPreaviso;
        $receiver['contact'] = $receiverContact;
        $receiverAddress = array();

        if (isset($post['rec_name'])) {
            $receiverAddress['name'] = $post['rec_name'];
        }
        if (isset($post['rec_postcode'])) {
            $receiverAddress['postcode'] = $shipmentHelper->cleanPostalCode($post['rec_postcode']);
        }
        if (isset($post['rec_city'])) {
            $receiverAddress['city'] = $post['rec_city'];
        }
        if (isset($post['rec_street'])) {
            $receiverAddress['street'] = $post['rec_street'];
        }
        if (isset($post['rec_houseNumber'])) {
            $receiverAddress['houseNumber'] = $post['rec_houseNumber'];
        }
        if (isset($post['rec_apartmentNumber'])) {
            $receiverAddress['apartmentNumber'] = $post['rec_apartmentNumber'];
        }
        $receiver['address'] = $receiverAddress;
        $ship['receiver'] = $receiver;
        //END::DANE ODBIORCY
        if (isset($post['lm_sap'])) {
            $ship['servicePointAccountNumber'] = $post['lm_sap'];
        }
        $shipmentData['ship'] = $ship;
        //START:: shipmentInfo
        $shipmentInfo = array();
        $shipmentInfo['dropOffType'] = 'REGULAR_PICKUP';
        $shipmentInfo['serviceType'] = 'LM';

        $billing = array();
        if (isset($post['payer'])) {
            if ($post['payer'] == 'N') {
                $billing['shippingPaymentType'] = 'SHIPPER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            } elseif ($post['payer'] == 'O') {
                $billing['shippingPaymentType'] = 'RECEIVER';
                $billing['paymentType'] = 'CASH';
            } elseif($post['payer'] == 'T') {
                $billing['shippingPaymentType'] = 'USER';
                $billing['paymentType'] = 'BANK_TRANSFER';
            }
        }
        if (isset($magentoSender['sap'])) {
            $billing['billingAccountNumber'] = $magentoSender['sap'];
        }
        if (isset($post['costsCenter'])) {
            $billing['costsCenter'] = $post['costsCenter'];
        }
        $shipmentInfo['billing'] = $billing;

        $specialServices = array();
        if (isset($post['insurance']) && $post['insurance'] == '1') {
            $item = array();
            $item['serviceType'] = 'UBEZP';
            if (isset($post['insuranceValue'])) {
                $item['serviceValue'] = $post['insuranceValue'];
            }
            $specialServices[] = $item;
        }
        if (isset($post['collectOnDelivery']) && $post['collectOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'COD';
            if (isset($post['collectOnDeliveryValue'])) {
                $item['serviceValue'] = $post['collectOnDeliveryValue'];
            }
            $item['collectOnDeliveryForm'] = 'BANK_TRANSFER';
            $specialServices[] = $item;
        }
        if (isset($post['predeliveryInformation']) && $post['predeliveryInformation'] == '1') {
            $item = array();
            $item['serviceType'] = 'PDI';
            $specialServices[] = $item;
        }
        if (isset($post['returnOnDelivery']) && $post['returnOnDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'ROD';
            $specialServices[] = $item;
        }
        if (isset($post['proofOfDelivery']) && $post['proofOfDelivery'] == '1') {
            $item = array();
            $item['serviceType'] = 'POD';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryEvening']) && $post['deliveryEvening'] == '1') {
            $item = array();
            $item['serviceType'] = '1722';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryOnSaturday']) && $post['deliveryOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['pickupOnSaturday']) && $post['pickupOnSaturday'] == '1') {
            $item = array();
            $item['serviceType'] = 'NAD_SOBOTA';
            $specialServices[] = $item;
        }
        if (isset($post['selfCollect']) && $post['selfCollect'] == '1') {
            $item = array();
            $item['serviceType'] = 'ODB';
            $specialServices[] = $item;
        }
        if (isset($post['deliveryToNeighbour']) && $post['deliveryToNeighbour'] == '1') {
            $item = array();
            $item['serviceType'] = 'SAS';
            $specialServices[] = $item;
        }
        $shipmentInfo['specialServices'] = $specialServices;

        if (isset($post['shipmentDate'])) {
            $shipmentInfo['shipmentDate'] = $post['shipmentDate'];
        }
        $shipmentInfo['labelType'] = $magentoData['labelType'];
        $shipmentData['shipmentInfo'] = $shipmentInfo;
        //END:: shipmentInfo

        $pieceList = array();
        if (isset($post['Package'])&& is_array($post['Package']) && count($post['Package']) > 0) {
            foreach ($post['Package'] as $package) {
                $_package = array();
                if (isset($package['packageType'])) {
                    if ($package['packageType'] == 'ENVELOPE') {
                        $_package['type'] = $package['packageType'];
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                    } else {
                        $_package['type'] = $package['packageType'];
                        if (isset($package['weight'])) {
                            $_package['weight'] = $package['weight'];
                        }
                        if (isset($package['width'])) {
                            $_package['width'] = $package['width'];
                        }
                        if (isset($package['height'])) {
                            $_package['height'] = $package['height'];
                        }
                        if (isset($package['length'])) {
                            $_package['lenght'] = $package['length'];
                        }
                        if (isset($package['quantity'])) {
                            $_package['quantity'] = $package['quantity'];
                        }
                        if (isset($package['nonStandard'])) {
                            $_package['nonStandard'] = $package['nonStandard'];
                        }
                    }
                }
                if (count($_package) > 0) {
                    $pieceList[] = $_package;
                }
            }
        }
        $shipmentData['pieceList'] = $pieceList;
        if (isset($post['content'])) {
            $shipmentData['content'] = $post['content'];
        }
        if (isset($post['comment'])) {
            $shipmentData['comment'] = $post['comment'];
        }
        if (isset($post['reference'])) {
            $shipmentData['reference'] = $post['reference'];
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/servicepoint/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/servicepoint/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $shipment['authData'] = array('username' => $login, 'password' => $pass);
        $shipment['shipmentData'] = $shipmentData;

        $params['shipment'] = $shipment;
        $context = stream_context_create(array('http' => array('header' => 'DHLApiOrgin: MAGENTO')));
        $_instance = new \SoapClient(
            self::DHL_SERVICEPOINTAPI,
            array( 'keep-alive' => true, 'connection_timeout' => 3, 'stream_context' => $context )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $result = $_instance->createShipment( $params );

        return $result;
    }

    /** Metoda pobierajaca dostępne czasy przyjazdu kuriera
     * @param $date data zamawiania kuriera
     * @param $code - kod pocztowy
     * @return string komunikat z mozliwymi godzinami
     * @throws \Exception
     */
    public function getPostalCodeServices($date, $code) {

        $_instance = new \SoapClient(
            self::DHL_WEBAPI2,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/webapi/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/webapi/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            "authData" => array('username' => $login, 'password' => $pass),
            'postCode' => str_replace('-', '', $code),
            'pickupDate' => $date
        );
        $result = $_instance->getPostalCodeServices( $params );

        if ($result->getPostalCodeServicesResult->exPickupFrom == 'brak' || $result->getPostalCodeServicesResult->exPickupTo == 'brak') {
            $msg = 'Brak dostępnych godzin przyjazdu kuriera dla przesyłek ekspresowych.';
        } else {
            $msg = 'Możliwe godziny przyjazdu kuriera dla przesyłek ekspresowych: ' . $result->getPostalCodeServicesResult->exPickupFrom . ' - ' . $result->getPostalCodeServicesResult->exPickupTo . '.';
        }
        if ($result->getPostalCodeServicesResult->drPickupFrom == 'brak' || $result->getPostalCodeServicesResult->drPickupTo == 'brak') {
            $msg .= '<br />Brak dostępnych godzin przyjazdu kuriera dla przesyłek drobnicowych';
        } else {
            $msg .= '<br />Możliwe godziny przyjazdu kuriera dla przesyłek drobnicowych: ' . $result->getPostalCodeServicesResult->drPickupFrom . ' - ' . $result->getPostalCodeServicesResult->drPickupTo;
        }
        return $msg;
    }

    /** Metoda pobera dane na temat domyslnych ustawien magento z dhl24
     * dane są cachowane
     * @return array
     * @throws CException
     */
    public function getMagentoFromDHL() {
        $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
        try {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $cache = $om->get('Magento\Framework\App\CacheInterface');
            $magentoData = $cache->load("magentoDhlData");
//            if ($magentoData !== false) {
//                return array('error' => false, 'data' => unserialize($magentoData));
//            }

            $_instance = new \SoapClient(
                self::DHL_MAGENTOAPI,
                array( 'keep-alive' => true, 'connection_timeout' => 3 )
            );
            if( !$_instance ) {
                throw new \Exception( 'Brak połączenia z usługą' );
            }

            $login = $this->_scopeConfig->getValue('dhl24pl/webapi/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $pass = $this->_scopeConfig->getValue('dhl24pl/webapi/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $params = array(
                "authData" => array('username' => $login, 'password' => $pass),
            );
            $result = $_instance->getMagentoData( $params );


            $data = $shipmentHelper->prepareData($result->getMagentoDataResult);
            $cache->save(serialize($data), "magentoDhlData", array(), 60 * 60 * 24);
            return array('error' => false, 'data' => $data);
        } catch (\Exception $e) {
            return array('error' => $e->getMessage(), 'data' => '');
        }
    }

    /** Metoda do usuwania przesylki za pomoca api service point
     * @param $lp numer listu przewozowego
     * @return mixed
     * @throws \Exception
     */
    public function removeServicePointShipment($lp) {
        $_instance = new \SoapClient(
            self::DHL_SERVICEPOINTAPI,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/servicepoint/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/servicepoint/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            'shipment' => array(
                'authData' => array('username' => $login, 'password' => $pass),
                'shipment' => $lp
            )
        );
        $result = $_instance->deleteShipment( $params );
        return $result;
    }

    /** Metoda do usuwania przesylki za pomoca api webapi2
     * @param $lp numer listu przewozowego
     * @return mixed
     * @throws \Exception
     */
    public function removeWebapiShipment($lp) {
        $_instance = new \SoapClient(
            self::DHL_WEBAPI2,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/webapi/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/webapi/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            'authData' => array('username' => $login, 'password' => $pass),
            'shipment' => array(
                'shipmentIdentificationNumber' => $lp
            )
        );
        $result = $_instance->deleteShipment( $params );
        return $result;
    }

    /** Metoda generuje etykiete za pomoca api webapi2
     * @param $date data raportu
     * @param $type rodzaj raportu
     * @return mixed
     * @throws CException
     */
    public function generateWebapiPnp($date, $type) {
        $_instance = new \SoapClient(
            self::DHL_WEBAPI2,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/webapi/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/webapi/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            'pnpRequest' => array(
                'authData' => array('username' => $login, 'password' => $pass),
                'date' => $date,
                'type' => $type
            )
        );
        $result = $_instance->getPnp( $params );
        return $result;
    }

    /** Metoda generuje etykiete za pomoca api servicepoint
     * @param $date data raportu
     * @return mixed
     * @throws CException
     */
    public function generateServicepointPnp($date) {
        $_instance = new \SoapClient(
            self::DHL_SERVICEPOINTAPI,
            array( 'keep-alive' => true, 'connection_timeout' => 3 )
        );
        if( !$_instance ) {
            throw new \Exception( 'Brak połączenia z usługą' );
        }
        $login = $this->_scopeConfig->getValue('dhl24pl/servicepoint/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/servicepoint/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = array(
            'structure' => array(
                'authData' => array('username' => $login, 'password' => $pass),
                'shipmentDate' => $date,
            )
        );
        $result = $_instance->getPnp( $params );
        return $result;
    }

    public function getLmmap() {
        return self::MAP_URL;
    }

    public function getEuropemap() {
        return self::MAP_URL;
    }

}