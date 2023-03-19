<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Sender
 * @package DHL\Dhl24pl\Model\ResourceModel
 */
class Sender extends AbstractDb
{

    /**
     * Package constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('dhl_senders', 'id');
    }

}
