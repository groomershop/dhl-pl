<?php

namespace DHL\Dhl24pl\Model\Checkout;

class ShippingInformationManagementPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $method = $addressInformation->getShippingMethodCode();
        $carrier = $addressInformation->getShippingCarrierCode();
        if ($carrier == 'dhl_dhl24pl') {
            $extAttributes = $addressInformation->getExtensionAttributes();
            $quote = $this->quoteRepository->getActive($cartId);
            if ($method == 'parcelshop') {
                file_put_contents('C:\testp.txt', json_encode(array('sap' => $extAttributes->getParceslhopSap())));
                $quote->setDhlplParcelshop(json_encode(array('sap' => $extAttributes->getParceslhopSap())));
            } elseif ($method == 'courier') {
                $params = array(
                    'neighbor_name' => $extAttributes->getCourierNeighborName(),
                    'neighbor_postcode' => $extAttributes->getCourierNeighborPostcode(),
                    'neighbor_city' => $extAttributes->getCourierNeighborCity(),
                    'neighbor_street' => $extAttributes->getCourierNeighborStreet(),
                    'neighbor_houseNumber' => $extAttributes->getCourierNeighborHouseNumber(),
                    'neighbor_apartmentNumber' => $extAttributes->getCourierNeighborApartmentNumber(),
                    'neighbor_phoneNumber' => $extAttributes->getCourierNeighborPhoneNumber(),
                    'neighbor_emailAddress' => $extAttributes->getCourierNeighborEmailAddress(),
                );
                file_put_contents('C:\testccc.txt', json_encode($params));
                $quote->setDhlplNeighbor(json_encode($params));
            }
        }
    }
}