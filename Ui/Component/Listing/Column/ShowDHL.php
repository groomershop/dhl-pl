<?php
namespace DHL\Dhl24pl\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\Template;

class ShowDHL extends Column
{

/**
* @var UrlInterface
*/
    protected $urlBuilder;
    protected $block;

    /**
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Template $block,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        if (!$authorization->isAllowed('DHL_Dhl24pl::dhlpl')) {
            $data = [];
        }
        $this->block = $block;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $button = $this->block->getViewFileUrl('DHL_Dhl24pl::images/DHL_button_PL.png');
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
                if (empty($item['dhlpl_settings'])) {
                    $item[$fieldName] = '<a href="' . $url . '" ><img style="height: 20px; max-width: 200px;" src="'.$button.'" title="Utwórz list przewozowy"/></a>';
                } else {
                    $dhlpl = json_decode($item['dhlpl_settings']);
                    if (isset($dhlpl->lp) && !empty($dhlpl->lp)) {
                        $url1 = $this->urlBuilder->getUrl(
                            'dhl/shipment/remove',
                            [
                                'order_id' => $item['entity_id']
                            ]
                        );
                        $url2 = 'http://www.dhl.com.pl/sledzenieprzesylkikrajowej/szukaj.aspx?m=0&sn=' . $dhlpl->lp;
                        $item[$fieldName] = '<a href="'.$url1.'" >Usuń przesyłkę ' . $dhlpl->lp . '</a><br /><a href="'.$url2.'" target="_BLANK">Znajdź przesyłkę ' . $dhlpl->lp . '</a>';
                    } else {
                        $item[$fieldName] = '';
                    }

                }
            }
        }

        return $dataSource;
    }
}