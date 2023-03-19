<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Plugin\Checkout;

use JMS\Serializer\Tests\Fixtures\Discriminator\Car;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Model\QuoteRepository;
use DHL\Dhl24pl\Model\Carrier\Carrier;

/**
 * Class ShippingInformationManagementPlugin
 * @package DHL\Dhl24pl\Plugin\Checkout
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $method = $addressInformation->getShippingMethodCode();
        $carrier = $addressInformation->getShippingCarrierCode();
        if ($carrier == Carrier::CARRIER_CODE) {
            $extAttributes = $addressInformation->getExtensionAttributes();
            $quote = $this->quoteRepository->getActive($cartId);
            if ($method == Carrier::CARRIER_PARCELSHOP_CODE || $method == Carrier::CARRIER_PARCELSHOP_COD_CODE) {
                $quote->setDhlplParcelshop(json_encode(array(
                    'sap' => $extAttributes->getParcelshopSap(),
                    'postcode' => $extAttributes->getParcelshopZip(),
                    'city' => $extAttributes->getParcelshopCity()
                )));
            } elseif ($method == Carrier::CARRIER_COURIER_CODE || $method == Carrier::CARRIER_COURIER_DHL12 || $method == Carrier::CARRIER_COURIER_EVENING || $method == Carrier::CARRIER_COURIER_COD) {
                if($extAttributes->getIsNeighbours() == 'true') {
                    $params = array(
                        'is_neighbours' => $extAttributes->getIsNeighbours(),
                        'neighbor_name' => $extAttributes->getCourierNeighborName(),
                        'neighbor_postcode' => $extAttributes->getCourierNeighborPostcode(),
                        'neighbor_city' => $extAttributes->getCourierNeighborCity(),
                        'neighbor_street' => $extAttributes->getCourierNeighborStreet(),
                        'neighbor_houseNumber' => $extAttributes->getCourierNeighborHouseNumber(),
                        'neighbor_apartmentNumber' => $extAttributes->getCourierNeighborApartmentNumber(),
                        'neighbor_phoneNumber' => $extAttributes->getCourierNeighborPhoneNumber(),
                        'neighbor_emailAddress' => $extAttributes->getCourierNeighborEmailAddress(),
                    );
                    $quote->setDhlplNeighbor(json_encode($params));
                }
            }
        }
    }
}
