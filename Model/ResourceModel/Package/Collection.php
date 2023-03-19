<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\ResourceModel\Package;

/**
 * Class Collection
 * @package DHL\Dhl24pl\Model\ResourceModel\Package
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'dhl_packages_collection';
    protected $_eventObject = 'packages_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('DHL\Dhl24pl\Model\Package', 'DHL\Dhl24pl\Model\ResourceModel\Package');
    }

}
