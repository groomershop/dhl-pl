<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\ResourceModel\Sender;

/**
 * Class Collection
 * @package DHL\Dhl24pl\Model\ResourceModel\Post
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'dhl_senders_collection';
    protected $_eventObject = 'senders_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('DHL\Dhl24pl\Model\Sender', 'DHL\Dhl24pl\Model\ResourceModel\Sender');
    }

}
