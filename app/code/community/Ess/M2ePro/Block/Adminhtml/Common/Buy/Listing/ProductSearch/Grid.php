<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_ProductSearch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $productId;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->productId = Mage::helper('M2ePro/Data_Global')->getValue('product_id');

        // Initialization block
        //------------------------------
        $this->setId('buyProductSearchGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $data = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $results = new Varien_Data_Collection();
        foreach ($data['data'] as $index => $item) {
            $temp = array(
                'id' => $index,
                'general_id' => isset($item['general_id']) ? $item['general_id'] : null,
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'price' => isset($item['price']) ? $item['price'] : null,
                'variations' => isset($item['variations']) ? $item['variations'] : null
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('image', array(
            'header'       => Mage::helper('M2ePro')->__('Image'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '80px',
            'index'        => 'image_url',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnImage')
        ));

        $this->addColumn('general_id', array(
            'header'       => Mage::helper('M2ePro')->__('Rakuten.com SKU'),
            'align'        => 'center',
            'type'         => 'text',
            'width'        => '75px',
            'index'        => 'general_id',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Product Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '375px',
            'string_limit' => 10000,
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle'),
        ));

        $this->addColumn('price',array(
            'header'       => Mage::helper('catalog')->__('Price'),
            'width'        => '60px',
            'align'        => 'right',
            'index'        => 'price',
            'filter'       => false,
            'sortable'     => false,
            'type'         => 'text',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('actions', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '78px',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
        ));

    }

    // ####################################

    public function callbackColumnImage($value, $product, $column, $isExport)
    {
        return '<img width="75px" src="'.$value.'" />';
    }

    public function callbackColumnGeneralId($value, $product, $column, $isExport)
    {
        if (empty($value)) {
            $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl('%general_id%');
            $templateHtml = '<a href="'.$url.'" target="_blank">%general_id%</a>';
            return '<span id="buy_link_'.$product->getId().'">' . Mage::helper('M2ePro')->__('N/A') . '</span>' .
                   '<div id="template_buy_link_'.$product->getId().'" style="display: none;">'.$templateHtml.'</div>';
        }

        $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($value);

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<div style="margin-left: 3px; margin-bottom: 10px;">'.
                        Mage::helper('M2ePro')->escapeHtml($value)."</div>";

        $variations = $row->getData('variations');
        if (is_null($variations)) {
            return $value;
        }

        $specificsHtml = '';
        $id = $row->getId();
        foreach ($variations['set'] as $specificName => $specific) {
            $specificsHtml .= '<span style="margin-left: 10px;
                                            font-size: 11px;
                                            color: #808080;
                                            display: inline-block;
                                            width: 100px;">'.
                                    ucfirst(strtolower($specificName)).
                              ':</span>';
            $specificsHtml .= '<select class="specifics_'.$id.'"
                                       onchange="ListingGridHandlerObj.productSearchHandler.specificsChange(this)"
                                       style="width: 150px; margin-left: 5px; margin-bottom: 5px; font-size: 10px;"
                                       id="specific_'.$specificName.'_'.$id.'">';
            $specificsHtml .= '<option value=""></option>';
            foreach ($specific as $option) {
                $specificsHtml .= '<option value="'.$option.'">'.$option.'</option>';
            }
            $specificsHtml .= '</select><br/>';
        }

        $specificsJsonContainer = '<div id="skus_'.$id.'" style="display: none;">'.
                                    json_encode($variations['skus']).'</div>';

        return $value . $specificsHtml . $specificsJsonContainer;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        return '<div id="price_'.$row->getId().'" style="margin-right: 5px;">'.$value.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign Rakuten.com SKU');
        //->__('There is no such variation on Rakuten.com. Please, choose another variation.');
        $naMessage = 'There is no such Variation on Rakuten.com. Please, choose another Variation.';
        $naMessage = Mage::helper('M2ePro')->__($naMessage);

        if (!is_null($row->getData('variations'))) {
            $templateMapHtml =
                '<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId('
                .$this->productId
                .', \'%general_id%\');">'.$assignText.'</a>';

            $templateNaHtml = '<a href="javascript:void(0);" onclick="alert(\''.$naMessage.'\')">'.$assignText.'</a>';

            return '<span id="map_link_'.$row->getId().'"><span style="color: #808080">'.$assignText.'</span></span>
                    <div id="template_map_link_'.$row->getId().'" style="display: none;">'.$templateMapHtml.'</div>
                    <div id="template_na_link_'.$row->getId().'" style="display: none;">'.$templateNaHtml.'</div>';
        }

        return '<a href="javascript:void(0);" onclick="ListingGridHandlerObj.productSearchHandler.mapToGeneralId('
            .$this->productId
            .', \''
            .$row->getData('general_id')
            .'\');">'.$assignText.'</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#buyProductSearchGrid div.grid th').each(function(el){
        el.style.padding = '2px 2px';
    });

    $$('#buyProductSearchGrid div.grid td').each(function(el){
        el.style.padding = '2px 2px';
    });

</script>
JAVASCRIPT;

        $searchData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $searchParamsHtml = <<<HTML
        <input id="buy_asin_search_type" type="hidden" value="{$searchData['type']}">
        <input id="buy_asin_search_value" type="hidden" value="{$searchData['value']}">
HTML;

        return parent::_toHtml() . $javascriptsMain . $searchParamsHtml;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_buy_listing/getSuggestedBuyComSkuGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}