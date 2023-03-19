<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class Sender
 * @package DHL\Dhl24pl\Controller\Adminhtml\Shipment
 */
class Sender extends \Magento\Backend\App\Action
{
    /**
     * @var SenderFactory
     */
    protected $senderFactory;

    /**
     * Sender constructor.
     * @param Action\Context $context
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        Action\Context $context,
        SenderFactory $senderFactory
    )
    {
        $this->senderFactory = $senderFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('DHL_Dhl24pl::dhlpl');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $name = $this->getRequest()->getParam('name');
        $senderData = $this->getSenderData();

        $result = [];
        if (isset($senderData[$name])) {
            $result = $senderData[$name];
        }

        $jsonData = json_encode($result);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * @return array
     */
    protected function getSenderData(): array
    {
        $senders = $this->senderFactory->create()->getCollection();
        $data = [];
        foreach ($senders->getData() as $sender) {
            $data[$sender['name']] = $sender;
        }

        return $data;
    }
}
