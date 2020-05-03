<?php
namespace DHL\Dhl24pl\Block\Adminhtml\Shipment;

class Create extends \Magento\Backend\Block\Template
{

    public $days;
    public $error;
    public $shipmentValues = array();

    public $magentoData = array();

    public $orderId;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getCountries() {
        $countries = array(
            'BE'=>'Belgia',
            'BG'=>'Bułgaria',
            'CY'=>'Cypr',
            'CZ'=>'Czechy',
            'DK'=>'Dania',
            'EE'=>'Estonia',
            'FI'=>'Finlandia',
            'FR'=>'Francja',
            'GR'=>'Grecja',
            'ES'=>'Hiszpania',
            'NL'=>'Holandia',
            'IE'=>'Irlandia',
            'LT'=>'Litwa',
            'LU'=>'Luksemburg',
            'MT'=>'Malta',
            'MC'=>'Monako',
            'DE'=>'Niemcy',
            'PT'=>'Portugalia',
            'RO'=>'Rumunia',
            'SE'=>'Szwecja',
            'SK'=>'Słowacja',
            'SI'=>'Słowenia',
            'GB'=>'Wielka Brytania',
            'IT'=>'Włochy',
            'HU'=>'Węgry',
            'LV'=>'Łotwa'
        );
        return $countries;
    }

    /** Metoda ustawia 3 nablizsze dni
     * @return array
     */
    public function generateDays() {
        $days = array();
        $days[date('Y-m-d')] = date('Y-m-d');
        $days[date('Y-m-d', strtotime("+1 day"))] = date('Y-m-d', strtotime("+1 day"));
        $days[date('Y-m-d', strtotime("+2 day"))] = date('Y-m-d', strtotime("+2 day"));
        return $days;
    }



    public function getShipmentsToSelect() {
        $magentoData = $this->getMagentoData();
        $shipments = array();
        if (isset($magentoData['shipments']) && is_array($magentoData['shipments']) && count($magentoData['shipments']) > 0) {
            foreach ($magentoData['shipments'] as $key =>$value) {
                $shipments[$value['name']] = $value['name'];
            }
        }
        return $shipments;
    }

    public function getSendersToSelect() {
        $magentoData = $this->getMagentoData();
        $shipments = array();
        if (isset($magentoData['senders']) && is_array($magentoData['senders']) && count($magentoData['senders']) > 0) {
            foreach ($magentoData['senders'] as $key =>$value) {
                $shipments[$value['name']] = $value['name'];
            }
        }
        return $shipments;
    }

    public function isSas() {
        $magentoData = $this->getMagentoData();
        if (isset($magentoData['sas']) && $magentoData['sas']) {
            return true;
        } else {
            return false;
        }
    }

    public function isEk() {
        $magentoData = $this->getMagentoData();
        if (isset($magentoData['ek']) && $magentoData['ek']) {
            return true;
        } else {
            return false;
        }
    }

    public function isLm() {

        $magentoData = $this->getMagentoData();
        if (isset($magentoData['lm']) && $magentoData['lm']) {
            return true;
        } else {
            return false;
        }
    }

    public function canCourier() {
        $magentoData = $this->getMagentoData();

        if (isset($magentoData['courier']) && $magentoData['courier']) {
            return true;
        } else {
            return false;
        }
    }

    public function fieldValue($data, $key) {
        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return '';
        }
    }

}
