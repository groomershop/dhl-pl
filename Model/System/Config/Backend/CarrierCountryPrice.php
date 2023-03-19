<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class CarrierCountryPrice
 * @package DHL\Dhl24pl\Model\System\Config\Backend
 */
class CarrierCountryPrice extends Value
{
    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * CarrierCountryPrice constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Random $mathRandom
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this|CarrierCountryPrice
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];
        foreach ($value as $data) {
            if (empty($data['country_id']) || empty($data['country_price'])) {
                continue;
            }
            $country = $data['country_id'];
            if (array_key_exists($country, $result)) {
                $result[$country] = $this->appendUniqueCountry($result[$country], $data['country_price']);
            } else {
                $result[$country] = $data['country_price'];
            }
        }
        $this->setValue(serialize($result));
        return $this;
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        $value = $this->getValue();

        if (empty($value) || is_null($value)) {
            return [];
        }

        if (in_array(substr($value, 0, 1), ['{', '[']) || in_array(substr($value, -1), ['}', ']'])) {
            $value = unserialize($value);
            $value = $this->encodeArrayFieldValue($value);
            $this->setValue($value);
        } else {
            $price[$this->mathRandom->getUniqueHash('_')] = ['country_id' => 'PL', 'country_price' => $value];
            $this->setValue($price);
        }
        return $this;
    }

    /**
     * @param array $value
     * @return array
     */
    protected function encodeArrayFieldValue(array $value): array
    {
        $result = [];
        foreach ($value as $country => $price) {
            $id = $this->mathRandom->getUniqueHash('_');
            $result[$id] = ['country_id' => $country, 'country_price' => $price];
        }
        return $result;
    }

    /**
     * @param array $countriesList
     * @param array $inputCountriesList
     * @return array
     */
    private function appendUniqueCountry(array $countriesList, array $inputCountriesList): array
    {
        $result = array_merge($countriesList, $inputCountriesList);
        return array_values(array_unique($result));
    }
}
