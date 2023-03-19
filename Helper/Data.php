<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use DHL\Dhl24pl\Helper\Api AS ApiHelper;
use Magento\Framework\App\CacheInterface;

class Data extends AbstractHelper
{
    const CACHE_IDENTIFIER = 'dhl24pl_countires';
    const REGULAR_PICKUP_CONFIG = 'dhl24pl/api/regular_pickup';

    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * @var CacheInterface
     */
    protected $cacheInterface;

    /**
     * Data constructor.
     * @param Context $context
     * @param ApiHelper $apiHelper
     * @param CacheInterface $cacheInterface
     */
    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        CacheInterface $cacheInterface
    )
    {
        $this->apiHelper = $apiHelper;
        $this->cacheInterface = $cacheInterface;

        parent::__construct($context);
    }

    /**
     * @return array
     * @throws \SoapFault
     */
    public function getCountries(): array
    {
        $countriesCache = $this->cacheInterface->load(self::CACHE_IDENTIFIER);

        if(!$countriesCache) {
            $countries = [];
            if(isset($this->apiHelper->getInternationalParams()->getInternationalParamsResult->params->item)) {
                $countries = $this->apiHelper->getInternationalParams()->getInternationalParamsResult->params->item;
            }
            $this->cacheInterface->save(serialize($countries), self::CACHE_IDENTIFIER, [], 60 * 60 * 24);

            return $countries;

        }

        return unserialize($countriesCache);
    }

    /**
     * @return array
     */
    public function getStoreInformation(): array
    {
        $store = [];
        $store['name'] = $this->scopeConfig->getValue('general/store_information/name');
        $store['phone'] = $this->scopeConfig->getValue('general/store_information/phone');
        $store['postcode'] = $this->scopeConfig->getValue('general/store_information/postcode');
        $store['city'] = $this->scopeConfig->getValue('general/store_information/city');
        $store['street_line1'] = $this->scopeConfig->getValue('general/store_information/street_line1');
        $store['street_line2'] = $this->scopeConfig->getValue('general/store_information/street_line2');
        $store['contact_person'] = $this->scopeConfig->getValue('general/store_information/contact_person');
        $store['sap'] = $this->scopeConfig->getValue('general/store_information/sap');
        $store['email'] = $this->scopeConfig->getValue('general/store_information/email');

        return $store;
    }

    /** Metoda do rozbijania frazu z ulicą, nr domu i nr lokalu na ulicę nr domu i nr lokalu jako tablice elementow
     *
     * @param $streetFull
     * @return array|bool
     */
    public function pregStreet($streetFull)
    {
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

    /**
     * @return bool
     */
    public function isRegularPickupEnable(): bool
    {
        return $this->scopeConfig->getValue(self::REGULAR_PICKUP_CONFIG) == '1' ? true : false;
    }
}
