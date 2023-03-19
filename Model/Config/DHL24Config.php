<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class DHL24Config
 * @package DHL\Dhl24pl\Model\Config
 */
class DHL24Config
{
    const WEBAPI_LOGIN = "dhl24pl/webapi/login";
    const WEBAPI_PASSWORD = "dhl24pl/webapi/password";

    const SERVICEPOINT_LOGIN = "dhl24pl/servicepoint/login";
    const SERVICEPOINT_PASSWORD = "dhl24pl/servicepoint/password";

    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->scopeConfig->getValue(self::WEBAPI_LOGIN, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->scopeConfig->getValue(self::WEBAPI_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getServicePointLogin()
    {
        return $this->scopeConfig->getValue(self::SERVICEPOINT_LOGIN, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getServicePointPassword()
    {
        return $this->scopeConfig->getValue(self::SERVICEPOINT_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $shouldUseServicePoint
     * @return array|array[]
     */
    public function getApiAuthorizationArray($shouldUseServicePoint = false): array
    {
        if ($shouldUseServicePoint) {
            return array(
                "authData" => array('username' => $this->getServicePointLogin(), 'password' => $this->getServicePointPassword()),
            );
        } else {
            return array(
                "authData" => array('username' => $this->getLogin(), 'password' => $this->getPassword()),
            );
        }
    }
}
