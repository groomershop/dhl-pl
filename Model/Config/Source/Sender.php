<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use DHL\Dhl24pl\Model\SenderFactory;

/**
 * Class Sender
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class Sender implements OptionSourceInterface
{

    /**
     * @var SenderFactory
     */
    protected $senderFactory;

    /**
     * Sender constructor.
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        SenderFactory $senderFactory
    )
    {
        $this->senderFactory = $senderFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $senderData = $this->getSenderData();
        $data = [];

        if (isset($senderData) && is_array($senderData) && count($senderData) > 0) {
            foreach ($senderData as $key => $value) {
                $data[] = [
                    'value' => $value['name'], 'label' => $value['name']
                ];
            }
        }

        return $data;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $senderData = $this->getSenderData();
        $data = [];

        if (isset($senderData) && is_array($senderData) && count($senderData) > 0) {
            foreach ($senderData as $key => $value) {
                $data[$value['name']] = $value['name'];
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getSenderData(): array
    {
        $senders = $this->senderFactory->create()->getCollection();
        $data[] = ['name' => '-- wybierz --'];
        foreach ($senders->getData() as $sender) {
            $data[] = ['name' => $sender['name']];
        }

        return $data;
    }
}
