<?php
namespace DHL\Dhl24pl\Ui\Component\Listing\Column;

use DHL\Dhl24pl\Model\Shipment\ShipmentType;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Dhl
 * @package DHL\Dhl24pl\Ui\Component\Listing\Column
 */
class Dhl extends Column
{
    const CREATE_SHIPMENT_URL_PATH = 'dhl/shipment/create';
    const DOWNLOAD_SHIPMENT_LABEL_URL_PATH = 'dhl/shipment/download';
    const DOWNLOAD_RETURN_SHIPMENT_LABEL_URL_PATH = 'dhl/shipment/download-return';
    const ENTITY_PARAM_NAME = 'order_id';
    const SHIPMENT_TYPE_PARAM_NAME = 'shipment_type';

    protected $urlBuilder;

    private $fieldName;

    /**
     * Dhl constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->fieldName = $this->getData('name');
    }

    /**
     * @param array $dataSource
     * @return array
     * TODO: refactor
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $viewUrlPath = 'dhl/shipment/create';
                $urlEntityParamName = 'order_id';
                $url = $this->urlBuilder->getUrl(
                    $viewUrlPath,
                    [
                        $urlEntityParamName => $item['entity_id']
                    ]
                );
                if (empty($item['dhlpl_settings']) || $item['dhlpl_settings'] == '{}') {
                    $item[$fieldName] = '<a href="' . $url . '" >Utwórz</a>';
                } else {
                    $dhlpl = json_decode($item['dhlpl_settings']);
                    if (isset($dhlpl->lp) && !empty($dhlpl->lp)) {
                        $url_remove = $this->urlBuilder->getUrl(
                            'dhl/shipment/remove',
                            [
                                'order_id' => $item['entity_id']
                            ]
                        );
                        $url_find = 'http://www.dhl.com.pl/sledzenieprzesylkikrajowej/szukaj.aspx?m=0&sn=' . $dhlpl->lp;

                        $url_shipment = $this->buildDownloadShipmentLink(
                            $item['entity_id'],
                            ShipmentType::SHIPMENT_TYPE_PRIMARY
                        );
                        $url_return_shipment = $this->buildDownloadShipmentLink(
                            $item['entity_id'],
                            ShipmentType::SHIPMENT_TYPE_RETURN
                        );

                        $item[$fieldName] = '<a href="'.$url_remove.'" >Usuń przesyłkę</a>';
                        $item[$fieldName] .= '<br/><a href="'.$url_find.'" target="_BLANK">Znajdź przesyłkę</a>';
                        $item[$fieldName] .= '<br/><a href="' . $url_shipment . '" >Pobierz etykietę</a>';

                        if (isset($dhlpl->returnlp) && !empty($dhlpl->returnlp)) {
                            $item[$fieldName] .= '<br/><a href="' . $url_return_shipment . '" >Pobierz etykietę zwrotną</a>';
                        }
                    } else {
                        $item[$fieldName] = '';
                    }
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param string $entityId
     * @param string $shipmentType
     * @return string
     */
    protected function buildDownloadShipmentLink(string $entityId, string $shipmentType): string
    {
        return $this->urlBuilder->getUrl(
            self::DOWNLOAD_SHIPMENT_LABEL_URL_PATH,
            [
                self::ENTITY_PARAM_NAME => $entityId,
                self::SHIPMENT_TYPE_PARAM_NAME => $shipmentType,
            ]
        );
    }
}
