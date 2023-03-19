<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class SaveDeliveryDateToOrderObserver
 * @package DHL\Dhl24pl\Model\Observer
 */
class SaveDeliveryDateToOrderObserver implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * SaveDeliveryDateToOrderObserver constructor.
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $order->setDhlplParcelshop($quote->getDhlplParcelshop());
        $order->setDhlplNeighbor($quote->getDhlplNeighbor());

        return $this;
    }
}
