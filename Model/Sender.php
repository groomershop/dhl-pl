<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Sender
 * @package DHL\Dhl24pl\Model
 */
class Sender extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'dhl_senders';

    protected $_cacheTag = 'dhl_senders';

    protected $_eventPrefix = 'dhl_senders';

    protected function _construct()
    {
        $this->_init('DHL\Dhl24pl\Model\ResourceModel\Sender');
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
