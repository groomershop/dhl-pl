<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Shipment;

/**
 * Class ReturnShipment
 * @package DHL\Dhl24pl\Model\Shipment
 */
class ReturnShipment
{
    protected string $bookCourier;
    protected string $serviceType;
    protected string $shippingPaymentType;
    protected int $billingAccountNumber;
}
