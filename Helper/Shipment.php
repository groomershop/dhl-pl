<?php namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Action\Action;

class Shipment extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_objectManager;

    protected $_scopeConfig;

    private $error;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getError() {
        return $this->error;
    }

    public function isError() {
        if (empty($this->error)) {
            return false;
        } else {
            return true;
        }
    }


    /** Metoda przetwarza 'surowe' dane z panelu magento w przystepną tablicę
     * @param $result
     * @return array
     */
    public function prepareData($result) {
        $data = array();
        $data['labelType'] = $result->labelType;
        $data['dropOffType'] = $result->dropOffType;
        $data['sas'] = $result->sas;
        $data['ek'] = $result->ek;
        $senders = array();
        if (isset($result->senders->item)) {
            if (is_array($result->senders->item)) {
                foreach ($result->senders->item as $key => $value) {
                    $sender = array();
                    $sender['name'] = $value->name;
                    $sender['sap'] = $value->sap;
                    $sender['personName'] = $value->personName;
                    $sender['phoneNumber'] = $value->phoneNumber;
                    $sender['emailAddress'] = $value->emailAddress;
                    $sender['postcode'] = $value->postcode;
                    $sender['city'] = $value->city;
                    $sender['street'] = $value->street;
                    $sender['houseNumber'] = $value->houseNumber;
                    $sender['apartmentNumber'] = $value->apartmentNumber;
                    $senders[$value->name] = $sender;
                }
            } else if(is_object($result->senders->item)) {
                $value = $result->senders->item;
                $sender = array();
                $sender['name'] = $value->name;
                $sender['sap'] = $value->sap;
                $sender['personName'] = $value->personName;
                $sender['phoneNumber'] = $value->phoneNumber;
                $sender['emailAddress'] = $value->emailAddress;
                $sender['postcode'] = $value->postcode;
                $sender['city'] = $value->city;
                $sender['street'] = $value->street;
                $sender['houseNumber'] = $value->houseNumber;
                $sender['apartmentNumber'] = $value->apartmentNumber;
                $senders[$value->name] = $sender;
            }
        }
        $data['senders'] = $senders;

        $shipments = array();
        if (isset($result->shipments->item)) {
            if (is_array($result->shipments->item)) {
                foreach ($result->shipments->item as $key => $value) {
                    $shipment = array();
                    $shipment['name'] = $value->name;
                    $shipment['senderName'] = $value->senderName;
                    $shipment['default'] = $value->default;
                    $shipment['courier'] = $value->courier;
                    $shipment['product'] = $value->product;
                    $shipment['payer'] = $value->payer;
                    $shipment['packageType'] = $value->packageType;
                    $shipment['width'] = $value->width;
                    $shipment['height'] = $value->height;
                    $shipment['length'] = $value->length;
                    $shipment['weight'] = $value->weight;
                    $shipment['quantity'] = $value->quantity;
                    $shipment['nonStandard'] = $value->nonStandard;
                    $shipment['deliveryEvening'] = $value->deliveryEvening;
                    $shipment['deliveryOnSaturday'] = $value->deliveryOnSaturday;
                    $shipment['pickupOnSaturday'] = $value->pickupOnSaturday;
                    $shipment['collectOnDelivery'] = $value->collectOnDelivery;
                    $shipment['collectOnDeliveryValue'] = $value->collectOnDeliveryValue;
                    $shipment['insurance'] = $value->insurance;
                    $shipment['insuranceValue'] = $value->insuranceValue;
                    $shipment['returnOnDelivery'] = $value->returnOnDelivery;
                    $shipment['proofOfDelivery'] = $value->proofOfDelivery;
                    $shipment['selfCollect'] = $value->selfCollect;
                    $shipment['predeliveryInformation'] = $value->predeliveryInformation;
                    $shipment['deliveryToNeighbour'] = $value->deliveryToNeighbour;
                    $shipment['deliveryToLM'] = $value->deliveryToLM;
                    $shipment['content'] = $value->content;
                    $shipment['comment'] = $value->comment;
                    $shipment['costsCenter'] = $value->costsCenter;
                    $shipments[$value->name] = $shipment;
                }
            } else if(is_object($result->shipments->item)){
                $value = $result->shipments->item;
                $shipment = array();
                $shipment['name'] = $value->name;
                $shipment['senderName'] = $value->senderName;
                $shipment['default'] = $value->default;
                $shipment['courier'] = $value->courier;
                $shipment['product'] = $value->product;
                $shipment['payer'] = $value->payer;
                $shipment['packageType'] = $value->packageType;
                $shipment['width'] = $value->width;
                $shipment['height'] = $value->height;
                $shipment['length'] = $value->length;
                $shipment['weight'] = $value->weight;
                $shipment['quantity'] = $value->quantity;
                $shipment['nonStandard'] = $value->nonStandard;
                $shipment['deliveryEvening'] = $value->deliveryEvening;
                $shipment['deliveryOnSaturday'] = $value->deliveryOnSaturday;
                $shipment['pickupOnSaturday'] = $value->pickupOnSaturday;
                $shipment['collectOnDelivery'] = $value->collectOnDelivery;
                $shipment['collectOnDeliveryValue'] = $value->collectOnDeliveryValue;
                $shipment['insurance'] = $value->insurance;
                $shipment['insuranceValue'] = $value->insuranceValue;
                $shipment['returnOnDelivery'] = $value->returnOnDelivery;
                $shipment['proofOfDelivery'] = $value->proofOfDelivery;
                $shipment['selfCollect'] = $value->selfCollect;
                $shipment['predeliveryInformation'] = $value->predeliveryInformation;
                $shipment['deliveryToNeighbour'] = $value->deliveryToNeighbour;
                $shipment['deliveryToLM'] = $value->deliveryToLM;
                $shipment['content'] = $value->content;
                $shipment['comment'] = $value->comment;
                $shipment['costsCenter'] = $value->costsCenter;
                $shipments[$value->name] = $shipment;
            }
        }
        $data['shipments'] = $shipments;
        return $data;
    }

    /***** METODY POMOCNICZE *****/

    /** Metoda sprawdza czy wybralismy opcje doreczenia do parceslhop, wywolywana jest api servicepoint
     * @param $allShipment - dane przesylki
     * @return bool
     */
    public function isLmShipment($allShipment) {
        if (isset($allShipment['deliveryToLM']) && $allShipment['deliveryToLM']) {
            return true;
        } else {
            return false;
        }
    }
    /** Metoda sprawdza czy dla zdefiniowanych danych mozna zamowic kuriera
     * @param $magentoData - dane z panelu magento z dhl
     * @param $shipment - nazwa wybranej przesylki, w przesylce jest tez informacja o dostepnosci kuriera
     * @return bool
     */
    public function canCourier($magentoData, $shipment) {
        if ((isset($magentoData['dropOffType']) && $magentoData['dropOffType'] == 'REQUEST_COURIER') || (isset($magentoData['shipments'][$shipment]) && isset($magentoData['shipments'][$shipment]['courier']) && $magentoData['shipments'][$shipment]['courier'])) {
            return true;
        } else {
            return false;
        }
    }

    /** Metoda pobiera dane odbiorcy z zamowienia
     * @param $orderId numer zamowienia w magento
     * @return array
     */
    public function getFromOrderData($model) {
        //$model = $this->getOrder($orderId);

        $data = array();
        $data['rec_name'] = $model->getShippingAddress()->getCompany() . ' ' . $model->getShippingAddress()->getFirstname().' '.$model->getShippingAddress()->getLastname();
        $data['rec_name'] = trim( $data['rec_name'] );
        $data['rec_name'] = substr( $data['rec_name'], 0, 60 );
        $data['rec_city'] = $model->getShippingAddress()->getCity();
        $data['rec_postcode'] = $model->getShippingAddress()->getPostcode();
        $street = $model->getShippingAddress()->getStreet();
        if (isset($street[0])) {
            $data['rec_street'] = $street[0];
            $streetFull = $street[0];
            if (isset($street[1])) {
                $streetFull .=  ' ' . $street[1];
            }
            if ($streetArray = $this->preg($streetFull)) {
                if (isset($streetArray['street'])) {
                    $data['rec_street'] = $streetArray['street'];
                }
                if (isset($streetArray['houseNumber'])) {
                    $data['rec_houseNumber'] = $streetArray['houseNumber'];
                }
                if (isset($streetArray['apartmentNumber'])) {
                    $data['rec_apartmentNumber'] = $streetArray['apartmentNumber'];
                }
            } else {
                $data['rec_houseNumber'] = '.';
            }
        }

        $data['rec_phoneNumber'] = $model->getShippingAddress()->getTelephone();
        $data['rec_emailAddress'] = $model->getCustomerEmail();

        $neighbour = $model->getDhlplNeighbor();
        if (!empty($neighbour)) {
            $neighbour = json_decode($neighbour);
            if (!empty($neighbour->neighbor_postcode) && !empty($neighbour->neighbor_city) && !empty($neighbour->neighbor_city)) {
                $data['neighbour_name'] = $neighbour->neighbor_name;
                $data['neighbour_postcode'] = $neighbour->neighbor_postcode;
                $data['neighbour_city'] = $neighbour->neighbor_city;
                $data['neighbour_street'] = $neighbour->neighbor_street;
                $data['neighbour_houseNumber'] = $neighbour->neighbor_houseNumber;
                $data['neighbour_apartmentNumber'] = $neighbour->neighbor_apartmentNumber;
                $data['neighbour_phoneNumber'] = $neighbour->neighbor_phoneNumber;
                $data['neighbour_emailAddress'] = $neighbour->neighbor_emailAddress;
                $data['deliveryToNeighbour'] = 1;
            }

        }
        $parcelshop = $model->getDhlplParcelshop();
        if (!empty($parcelshop)) {
            $parcelshop = json_decode($parcelshop);
            $data['lm_sap'] = $parcelshop->sap;
            $data['deliveryToLM'] = 1;
        }
        //sprawdzenie czy została wybrana opcja cashondelivery
        $type = $this->_scopeConfig->getValue('dhl24pl/cod/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $variant = $this->_scopeConfig->getValue('dhl24pl/cod/variant', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $payment = $model->getPayment();
        $paymentType = $payment->getMethod();
        $price = false;
        if ($type == 'cashondelivery') {//jest wlaczona opcja COD
            if ($type == $paymentType) {//platnosc zgadza sie z typem
                if ($variant == 'all') {
                    $price = $model->getGrandTotal();
                } elseif ($variant == 'products') {
                    $price = $model->getSubtotalInclTax();
                }
            }
        }
        if ($price !== false) {//jest COD
            $data['insurance'] = 1;
            $data['insuranceValue'] = number_format($price, 2, '.', '');
            $data['collectOnDelivery'] = 1;
            $data['collectOnDeliveryValue'] = number_format($price, 2, '.', '');
            $data['isPrice'] = true;
        } else {
            $data['isPrice'] = false;
        }

        return $data;
    }

    /** Metoda sprawdza czy przesylka juz zostala utworzona dla danego zamowienia
     * @param $id numer zamowienia z magento
     * @return bool
     */
    public function isAlreadyCreated($order) {
        if ($order) {
            $shipment = $order->getDhlplSettings();
            if (empty($shipment)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /** Metoda pobiera zamowienie z magenyo
     * @param $oid numer zamowienia z magento
     * @return bool
     */
    public function getOrder($id) {
        $orderRepository = $this->_objectManager
            ->get('Magento\Sales\Api\OrderRepositoryInterface');
        $order = $orderRepository->get($id);
        if ($order->getId()) {
            return $order;
        } else {
            return false;
        }
    }

    /** Metoda sprawdza czy istnieje zamowienie o podanym id
     * @param $id numer zamowienia
     * @return bool
     */
    public function isOrder($order) {
        if ($order) {
            return true;
        } else {
            return false;
        }
    }

    /** Metoda na podstawie danych konfiguracyjnych sprawdza czy jest dostep do LM
     *
     */
    public function isLm() {
        $login = $this->_scopeConfig->getValue('dhl24pl/servicepoint/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->_scopeConfig->getValue('dhl24pl/servicepoint/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (empty($login) || empty($pass)) {
            return false;
        } else {
            return true;
        }
    }

    public function cleanPostalCode($code) {
        return str_replace('-', '', $code);
    }

    /** Metoda do rozbijania frazu z ulicą, nr domu i nr lokalu na ulicę nr domu i nr loklau jako tablice elemtnow
     * @param $streetFull
     * @return array|bool
     */
    public function preg($streetFull) {


        $data = array('street' => '', 'houseNumber' => '', 'apartmentNumber' => '');
        $streetFull = trim($streetFull);

        $pregLokal = 'l[\.]?|lo[\.]?|lok[\.]?|loka[\.]?|lokal[\.]?';
        $pregMieszkanie = 'm\.?|mi\.?|mie\.?|mies\.?|miesz\.?|mieszk\.?|mieszka\.?|mieszkan\.?|mieszkani\.?|mieszkanie\.?|mieszkania\.?';
        $pregLacznik = '(\/|\s|' . $pregLokal . '|' . $pregMieszkanie .'|przez){1}';
        $pregNrDomu = '(\d+[\/]?\d*[a-zA-Z]{0,3})';
        $pregNrMieszkania = '([a-zA-Z]{0,3}\d+[a-zA-Z]{0,3})';

        if ( preg_match('/^(.*)(?!(' . $pregNrDomu . '[\s]*' . $pregLacznik . '[\s]*' . $pregNrMieszkania . '))[\s]*' . $pregNrDomu . '[\s]*' . $pregLacznik . '[\s]*' . $pregNrMieszkania . '$/ ', $streetFull, $wynik) ) {
            if (isset($wynik[1])) {
                $data['street'] = $wynik[1];
            }
            if (isset($wynik[6])) {
                $data['houseNumber'] = $wynik[6];
            }
            if (isset($wynik[8])) {
                $data['apartmentNumber'] = $wynik[8];
            }
        } elseif (preg_match('/^([^\d]*[^\d\s])[\s]*' . $pregNrDomu . '[\s]*' . $pregLacznik . '[\s]*' . $pregNrMieszkania . '$/ ', $streetFull, $wynik)) {
            if (isset($wynik[1])) {
                $data['street'] = $wynik[1];
            }
            if (isset($wynik[2])) {
                $data['houseNumber'] = $wynik[2];
            }
            if (isset($wynik[4])) {
                $data['apartmentNumber'] = $wynik[4];
            }
        }else if (preg_match('/^(.*)(?!(' . $pregNrDomu . '))[\s]*' . $pregNrDomu . '$/ ', $streetFull, $wynik) ) {
            if (isset($wynik[1])) {
                $data['street'] = $wynik[1];
            }
            if (isset($wynik[4])) {
                $data['houseNumber'] = $wynik[4];
            }
        }  else if ( preg_match('/^([^\d]*[^\d\s])[\s]*(\d.*)$/ ', $streetFull, $wynik) ) {
            if (isset($wynik[1])) {
                $data['street'] = $wynik[1];
            }
            if (isset($wynik[2])) {
                $data['houseNumber'] = $wynik[2];
            }
        } else {
            $data = false;
        }
        return $data;
    }

    public function prepareAllShipment(& $magentoData, $order) {
//        //ustawiamy informacje czy dostepne LM
        $magentoData['lm'] = $this->isLm();

        //pobieramy domyslna przesylke
        $shipment = array();
        if (isset($magentoData['shipments']) && is_array($magentoData['shipments']) && count($magentoData['shipments']) > 0) {
            foreach ($magentoData['shipments'] as $key => $value) {
                $shipment = $value;
                break;
            }
        }

        $shipmentName = '';
        if (isset($shipment['name'])) {
            $shipmentName = $shipment['name'];
        }
        //sprawdzamy czy mozemy nadac kuriera
        if ($this->canCourier($magentoData, $shipmentName)) {
            $magentoData['courier'] = true;
        } else {
            $magentoData['courier'] = false;
        }
        //pobieramy nadawce
        $sender = array();
        if (isset($shipment['senderName']) && isset($magentoData['senders'][$shipment['senderName']])) {
            $sender = $magentoData['senders'][$shipment['senderName']];
        } else {
            foreach ($magentoData['senders'] as $key => $value) {
                $sender = $value;
                break;
            }
        }
        //zmianna dla platnik 3 strona
        $sender['name2'] = '';

        //pobieramy dane odbiorcy
        $reciver = $this->getFromOrderData($order);

        //pobieranie domyslnej paczki
        $shipment['Package'] = array();
        if (isset($shipment['packageType'])) {
            $package = array();
            $package['packageType'] = $shipment['packageType'];
            $package['width'] = $shipment['width'];
            $package['height'] = $shipment['height'];
            $package['length'] = $shipment['length'];
            $package['weight'] = $shipment['weight'];
            $package['quantity'] = $shipment['quantity'];
            $package['nonStandard'] = $shipment['nonStandard'];
            $shipment['Package'][] = $package;
        }
        $shipment['comment'] = $order->getIncrementId();
        $shipment['shipmentDate'] = date('Y-m-d');
        //ustawiamy domyslnego platnika
        if (!isset($shipment['payer']) || empty($shipment['payer'])) {
            $shipment['payer'] = 'N';
        }

        //tworzymy tablice z wszytskimi danymi
        return array_merge($shipment, array_merge($reciver, $sender));
    }

    public function prepareAllShipmentAfterPost(& $magentoData, $data, $post) {
        //pobieramy dane nadawcy gdy na formularzy jest disabled, nie ma w $_POST
        $result = array();
        if (isset($post['payer']) && ($post['payer'] == 'N' || $post['payer'] == 'O')) {
            $name = $post['name'];
            if (isset($magentoData['senders'][$name])) {
                $result = $magentoData['senders'][$name];
            }
        }

        //ustawimy czy mozna zamowic kuriera
        $shipmentName = '';
        if (isset($data['shipment'])) {
            $shipmentName = $data['shipment'];
        }
        if ($this->canCourier($magentoData, $shipmentName)) {
            $magentoData['courier'] = true;
        } else {
            $magentoData['courier'] = false;
        }

        return array_merge($data, $result);
    }

    public function prepareMagentoSender($magentoData) {
        //dane nadawcy z panelu magento - do sekcji billing
        $magentoSender = false;
        if (isset($_POST['name'])) {
            if (isset($magentoData['senders'][$_POST['name']])) {
                $magentoSender = $magentoData['senders'][$_POST['name']];
            }
        }
        if ($magentoSender === false) {
            foreach ($magentoData['senders'] as $key => $value) {
                $magentoSender = $value;
                break;
            }
        }
        return $magentoSender;
    }

    public function validate($post) {
        if (!isset($post['payer']) || empty($post['payer'])) {
            $this->setError('Brak wybranego płatnika');
            return false;
        }
        if (!isset($post['product']) || empty($post['product'])) {
            $this->setError('Brak wybranego produktu');
            return false;
        }
        if (isset($post['deliveryToLM']) && $post['deliveryToLM'] && in_array($post['product'], array('EK', '09', '12'))) {
            $this->setError('Doręczenia do Parcelshop można wybrać tylko dla przesyłek DHL PARCEL POLSKA');
            return false;
        }
        if (isset($post['deliveryToLM']) && $post['deliveryToLM'] && isset($post['deliveryToNeighbour']) && $post['deliveryToNeighbour']) {
            $this->setError('Nie można wybrać równoczesnie doręczenia do Parcelshop i doręczenia do sąsiada');
            return false;
        }
        return true;
    }

    public function saveShipmentParams($orderId, $params) {
        $order = $this->getOrder($orderId);
        $order->setDhlplSettings($params);
        $order->save();
    }

    /** Metoda czyści nr telefonu tak aby były same cyfry
     * @param $number
     * @return mixed
     */
    public function clearPhone($number) {
        $number = str_replace(array(' ', '+', '-'), array('', '', ''), $number);
        return $number;
    }
}