<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Package
 * @package DHL\Dhl24pl\Model
 */
class Package extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'dhl_packages';

    protected $_cacheTag = 'dhl_packages';

    protected $_eventPrefix = 'dhl_packages';

    protected function _construct()
    {
        $this->_init('DHL\Dhl24pl\Model\ResourceModel\Package');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
