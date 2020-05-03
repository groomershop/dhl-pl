<?php namespace DHL\Dhl24pl\Helper;

use Magento\Framework\App\Action\Action;

class Pnp extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_objectManager;

    protected $_scopeConfig;

    private $error;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getError() {
        return $this->error;
    }

    public function isError() {
        if (empty($this->error)) {
            return false;
        } else {
            return true;
        }
    }


    public function validate($post) {
        if (!isset($post['kind']) || !in_array($post['kind'], array('WEBAPI', 'SERVICEPOINT'))) {
            $this->setError('Rodzaj raportu jest nieporpawny');
            return false;
        }
        if ($post['kind'] == 'WEBAPI') {
            if (!isset($post['type']) ||!in_array($post['type'], array('EX', 'DR', 'ALL', '2EUROPE'))) {

                $this->setError('Typ raportu jest nieporpawny');
                return false;
            }
        }
        if ($post['kind'] == 'SERVICEPOINT') {
            $shipmentHelper = $this->_objectManager->get('DHL\Dhl24pl\Helper\Shipment');
            if (!$shipmentHelper->isLm()) {
                $this->setError('Brak zdefiniowanych dostępów do api ParcelShop');
                return false;
            }
        }
        $date = isset($post['date']) ? $post['date'] : '';
        if ( empty($date)) {
            $this->setError('Należy podać datę w formacie yyyy-mm-dd');
            return false;
        }
        return true;
    }
}