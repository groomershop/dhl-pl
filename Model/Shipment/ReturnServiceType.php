<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Shipment;

use MyCLabs\Enum\Enum;

/**
 * Class ReturnServiceType
 * @package DHL\Dhl24pl\Model\Shipment
 */
class ReturnServiceType extends Enum
{
    const RETURN_SERVICE_TYPE_CONNECT = 'ZC';
    const RETURN_SERVICE_TYPE_DOMESTIC = 'ZK';
}
