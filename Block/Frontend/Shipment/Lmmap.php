<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Block\Frontend\Shipment;

use Magento\Framework\View\Element\Template\Context;
use DHL\Dhl24pl\Helper\Api AS ApiHelper;

/**
 * Class Lmmap
 * @package DHL\Dhl24pl\Block\Frontend\Shipment
 */
class Lmmap extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /**
     * Lmmap constructor.
     * @param Context $context
     * @param ApiHelper $apiHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ApiHelper $apiHelper,
        array $data = []
    )
    {
        $this->apiHelper = $apiHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getLmmapUrl() {
        $country = $this->_request->getParam('country');
        $url = $this->apiHelper->getLmmap();

        if($country) {
            $url .= '?country='.$country;
        }

        return $url;
    }
}
