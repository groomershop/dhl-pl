<?php

namespace DHL\Dhl24pl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Date
 * @package DHL\Dhl24pl\Model\Config\Source
 */
class Date implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' =>  $this->createDayZero(), 'label' =>  $this->createDayZero()],
            ['value' =>  $this->createDateOne(), 'label' =>  $this->createDateOne()],
            ['value' =>  $this->createDateTwo(), 'label' =>  $this->createDateTwo()],
            ['value' =>  $this->createDateThree(), 'label' =>  $this->createDateThree()],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            $this->createDayZero() => $this->createDayZero(),
            $this->createDateOne() => $this->createDateOne(),
            $this->createDateTwo() => $this->createDateTwo(),
            $this->createDateThree() => $this->createDateThree()
        ];
    }

    /**
     * @return false|string
     */
    protected function createDayZero()
    {
        return $date = date('Y-m-d');
    }

    /**
     * @return false|string
     */
    protected function createDateOne()
    {
        return $date = date('Y-m-d', strtotime("+1 day"));
    }

    /**
     * @return false|string
     */
    protected function createDateTwo()
    {
        return $date = date('Y-m-d', strtotime("+2 day"));
    }

    /**
     * @return false|string
     */
    protected function createDateThree()
    {
        return $date = date('Y-m-d', strtotime("+3 day"));
    }
}
