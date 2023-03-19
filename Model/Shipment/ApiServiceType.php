<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Shipment;

use MyCLabs\Enum\Enum;

/**
 * @method static MAGENTO_API()
 * @method static SERVICE_POINT_API()
 * @method static WEB_API2()
 */
class ApiServiceType extends Enum
{
    private const WEB_API2 = 'web_api2';
    private const MAGENTO_API = 'magento_api';
    private const SERVICE_POINT_API = 'service_point_api';
}
