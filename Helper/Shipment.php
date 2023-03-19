<?php namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Shipment extends AbstractHelper
{
    protected $error;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepositoryInterface;

    /**
     * Shipment constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderRepositoryInterface $orderRepositoryInterface
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepositoryInterface
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepositoryInterface = $orderRepositoryInterface;

        parent::__construct($context);
    }

    /**
     * @param $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        if (empty($this->error)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $result
     * @return array
     */
    public function prepareData($result)
    {
        $data = [];
        $data['labelType'] = $result->labelType;
        $data['dropOffType'] = $result->dropOffType;
        $data['sas'] = $result->sas;
        $data['ek'] = $result->ek;
        $senders = [];
        $sender = [];
        if (isset($result->senders->item)) {
            if (is_array($result->senders->item)) {
                foreach ($result->senders->item as $key => $value) {
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

        $shipments = [];
        $shipment = [];
        if (isset($result->shipments->item)) {
            if (is_array($result->shipments->item)) {
                foreach ($result->shipments->item as $key => $value) {
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

    /**
     * @param $magentoData
     * @param $shipment
     * @return bool
     */
    public function canCourier($magentoData, $shipment): bool
    {
        if ((isset($magentoData['dropOffType']) && $magentoData['dropOffType'] == 'REQUEST_COURIER') || (isset($magentoData['shipments'][$shipment]) && isset($magentoData['shipments'][$shipment]['courier']) && $magentoData['shipments'][$shipment]['courier'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $model
     * @return array
     */
    public function getFromOrderData($model): array
    {
        $data = [];
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

        $type = $this->scopeConfig->getValue('dhl24pl/cod/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $variant = $this->scopeConfig->getValue('dhl24pl/cod/variant', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $payment = $model->getPayment();
        $paymentType = $payment->getMethod();
        $price = false;
        if ($type == 'cashondelivery') {
            if ($type == $paymentType) {
                if ($variant == 'all') {
                    $price = $model->getGrandTotal();
                } elseif ($variant == 'products') {
                    $price = $model->getSubtotalInclTax();
                }
            }
        }
        if ($price !== false) {
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

    /**
     * @param $id
     * @return bool|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($id)
    {
        $order = $this->orderRepositoryInterface->get($id);
        if ($order->getId()) {
            return $order;
        } else {
            return false;
        }
    }

    /**
     * @param $order
     * @return bool
     */
    public function isOrder($order): bool
    {
        if ($order) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isLm(): bool
    {
        $login = $this->scopeConfig->getValue('dhl24pl/servicepoint/login', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $pass = $this->scopeConfig->getValue('dhl24pl/servicepoint/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (empty($login) || empty($pass)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $code
     * @return string|string[]
     */
    public function cleanPostalCode($code): string
    {
        return str_replace('-', '', $code);
    }

    /**
     * @param $streetFull
     * @return string|array
     */
    public function preg($streetFull)
    {
        $data = ['street' => '', 'houseNumber' => '', 'apartmentNumber' => ''];
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
            $data = '';
        }

        return $data;
    }

    /**
     * @param $magentoData
     * @param $data
     * @return array
     */
    public function prepareAllShipmentAfterPost(& $magentoData, $data): array
    {
        $result = [];
        if (isset($data['payer']) && ($data['payer'] == 'N' || $data['payer'] == 'O') && array_key_exists('name', $data['sender'])) {
            $name = $data['sender']['name'];
            if (isset($magentoData['data']['senders'][0][$name])) {
                $result = $magentoData['data']['senders'][0][$name];
            }
        }

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

    /**
     * @param $post
     * @return bool
     */
    public function validate($post): bool
    {
        if (!isset($post['payer']) || empty($post['payer'])) {
            $this->setError('Brak wybranego płatnika');
            return false;
        }
        if (!isset($post['package_product_type']['product_type']) || empty($post['package_product_type']['product_type'])) {
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

    /**
     * @param $orderId
     * @param $params
     */
    public function saveShipmentParams($orderId, $params)
    {
        $order = $this->getOrder($orderId);
        $order->setDhlplSettings($params);
        $order->save();
    }

    /**
     * @param $number
     * @return string
     */
    public function clearPhone($number): string
    {
        $number = str_replace(array(' ', '+', '-'), array('', '', ''), $number);
        return $number;
    }
}
