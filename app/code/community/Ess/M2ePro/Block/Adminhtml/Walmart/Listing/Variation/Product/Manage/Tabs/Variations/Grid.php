<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_Processor as ListProcessor;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_Manage_Tabs_Variations_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_childListingProducts     = null;
    protected $_currentProductVariations = null;
    protected $_usedProductVariations    = null;

    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    protected $_lockedDataCache = array();

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Walmart')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartVariationProductManageGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->distinct();
        $collection->getSelect()->where("`second_table`.`variation_parent_id` = ?", (int)$this->getListingProductId());

        $collection->getSelect()->columns(
            array(
            'online_price' => 'second_table.online_price'
            )
        );

        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr(
                '(
                SELECT
                    mlpv.listing_product_id,
                    GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`product_id` SEPARATOR \'||\') as products_ids
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` as mlpv
                INNER JOIN `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable() .
                    '` AS `mlpvo` ON (`mlpvo`.`listing_product_variation_id`=`mlpv`.`id`)
                WHERE `mlpv`.`component_mode` = \'walmart\'
                GROUP BY `mlpv`.`listing_product_id`
            )'
            ),
            'main_table.id=t.listing_product_id',
            array(
                'products_ids' => 'products_ids',
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
        $parentType = $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel();

        $channelAttributesSets = $parentType->getChannelAttributes();
        $productAttributes = $parentType->getProductAttributes();

        if ($parentType->hasMatchedAttributes()) {
            $productAttributes = array_keys($parentType->getMatchedAttributes());
            $channelAttributes = array_values($parentType->getMatchedAttributes());
        } else if (!empty($channelAttributesSets)) {
            $channelAttributes = array_keys($channelAttributesSets);
        } else {
            $channelAttributes = array();
        }

        $this->addColumn(
            'product_options', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Variation'),
            'align'     => 'left',
            'width' => '200px',
            'sortable' => false,
            'index'     => 'additional_data',
            'filter_index' => 'additional_data',
            'frame_callback' => array($this, 'callbackColumnProductOptions'),
            'filter'   => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $productAttributes,
            'filter_condition_callback' => array($this, 'callbackProductOptions')
            )
        );

        $this->addColumn(
            'channel_options', array(
            'header'    => Mage::helper('M2ePro')->__('Walmart Variation'),
            'align'     => 'left',
            'width' => '200px',
            'sortable' => false,
            'index'     => 'additional_data',
            'filter_index' => 'additional_data',
            'frame_callback' => array($this, 'callbackColumnChannelOptions'),
            'filter'   => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $channelAttributes,
            'filter_condition_callback' => array($this, 'callbackChannelOptions')
            )
        );

        $this->addColumn(
            'sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'sku',
            'filter_index' => 'sku',
            'frame_callback' => array($this, 'callbackColumnWalmartSku')
            )
        );

        $this->addColumn(
            'gtin', array(
            'header' => Mage::helper('M2ePro')->__('GTIN'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'gtin',
            'filter_index' => 'gtin',
            'frame_callback' => array($this, 'callbackColumnGtin')
            )
        );

        $this->addColumn(
            'online_qty', array(
            'header' => Mage::helper('M2ePro')->__('QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty'),
            'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'online_price',
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        );

        $this->addColumn('online_price', $priceColumn);

        $statusColumn = array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $isResetFilterShouldBeShown = Mage::helper('M2ePro/View_Walmart')->isResetFilterShouldBeShown(
            'variation_parent_id',
            $this->getListingProduct()->getId()
        );

        $isResetFilterShouldBeShown && $statusColumn['filter'] = 'M2ePro/adminhtml_walmart_grid_column_filter_status';

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $groups = array(
            'actions' => Mage::helper('M2ePro')->__('Actions'),
            'other'   => Mage::helper('M2ePro')->__('Other'),
        );
        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem(
            'list', array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'deleteAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Retire on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        // ---------------------------------------

        $this->getMassactionBlock()->addItem(
            'resetProducts', array(
                'label'    => Mage::helper('M2ePro')->__('Reset Inactive (Blocked) Item(s)'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnProductOptions($additionalData, $row, $column, $isExport)
    {
        $html = '';

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $row->getChildObject()->getVariationManager()->getTypeModel();

        $html .= '<div class="product-options-main" style="font-size: 11px; color: grey; margin-left: 7px">';
        $productOptions = $typeModel->getProductOptions();
        if (!empty($productOptions)) {
            $productsIds = $this->parseGroupedData($row->getData('products_ids'));
            $uniqueProductsIds = count(array_unique($productsIds)) > 1;

            $matchedAttributes = $typeModel->getParentTypeModel()->getMatchedAttributes();
            if (!empty($matchedAttributes)) {
                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $walmartAttr) {
                    $sortedOptions[$magentoAttr] = $productOptions[$magentoAttr];
                }

                $productOptions = $sortedOptions;
            }

            $virtualProductAttributes = array_keys($typeModel->getParentTypeModel()->getVirtualProductAttributes());

            $html .= '<div class="m2ePro-variation-attributes product-options-list">';
            if (!$uniqueProductsIds) {
                $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => reset($productsIds)));
                $html .= '<a href="' . $url . '" target="_blank">';
            }

            foreach ($productOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualProductAttributes)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                !$option && $option = '--';
                $optionHtml = '<span class="attribute-row" style="' . $style . '"><span class="attribute"><strong>' .
                    Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong></span>:&nbsp;<span class="value">' . Mage::helper('M2ePro')->escapeHtml($option) .
                    '</span></span>';

                if ($uniqueProductsIds && $option !== '--' && !in_array($attribute, $virtualProductAttributes)) {
                    $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productsIds[$attribute]));
                    $html .= '<a href="' . $url . '" target="_blank">' . $optionHtml . '</a><br/>';
                } else {
                    $html .= $optionHtml . '<br/>';
                }
            }

            if (!$uniqueProductsIds) {
                $html .= '</a>';
            }

            $html .= '</div>';
        }

        if ($this->canChangeProductVariation($row)) {
            $listingProductId = $row->getId();
            $attributes = array_keys($typeModel->getParentTypeModel()->getMatchedAttributes());
            $variationsTree = $this->getProductVariationsTree($row, $attributes);

            $linkTitle = Mage::helper('M2ePro')->__('Change Variation');
            $linkContent = Mage::helper('M2ePro')->__('Change Variation');

            $attributes = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->jsonEncode($attributes));
            $variationsTree = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->jsonEncode($variationsTree));

            $html .= <<<HTML
<form action="javascript:void(0);" class="product-options-edit"></form>
<a href="javascript:"
    onclick="ListingGridHandlerObj.editProductOptions(this, {$attributes}, {$variationsTree}, {$listingProductId})"
    title="{$linkTitle}">{$linkContent}</a>
HTML;
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnChannelOptions($additionalData, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $row->getChildObject();

        $typeModel = $walmartListingProduct->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $parentWalmartListingProduct */
        $parentWalmartListingProduct = $typeModel->getParentListingProduct()->getChildObject();

        $matchedAttributes = $parentWalmartListingProduct->getVariationManager()
            ->getTypeModel()
            ->getMatchedAttributes();

        $options = $typeModel->getChannelOptions();

        if (!empty($matchedAttributes)) {
            $sortedOptions = array();

            foreach ($matchedAttributes as $magentoAttr => $walmartAttr) {
                $sortedOptions[$walmartAttr] = $options[$walmartAttr];
            }

            $options = $sortedOptions;
        }

        if (empty($options)) {
            return '';
        }

        $gtin = $walmartListingProduct->getGtin();
        $itemId = $walmartListingProduct->getItemId();

        $virtualChannelAttributes = array_keys($typeModel->getParentTypeModel()->getVirtualChannelAttributes());

        $html = '<div class="m2ePro-variation-attributes" style="color: grey; margin-left: 7px">';

        if (!empty($gtin) && !empty($itemId)) {
            $walmartHelper = Mage::helper('M2ePro/Component_Walmart');
            $marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
            $url = $walmartHelper->getItemUrl(
                $walmartListingProduct->getData($walmartHelper->getIdentifierForItemUrl($marketplaceId)),
                $marketplaceId
            );

            $html .= '<a href="' . $url . '" target="_blank" title="' . $gtin . '" >';
        }

        foreach ($options as $attribute => $option) {
            $style = '';
            if (in_array($attribute, $virtualChannelAttributes)) {
                $style = 'border-bottom: 2px dotted grey';
            }

            !$option && $option = '--';

            $attrName = Mage::helper('M2ePro')->escapeHtml($attribute);
            $optionName = Mage::helper('M2ePro')->escapeHtml($option);

            $html .= <<<HTML
<span style="{$style}"><b>{$attrName}</b>:&nbsp;{$optionName}</span><br/>
HTML;
        }

        if (!empty($gtin) && !empty($itemId)) {
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnWalmartSku($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        $productId = $row->getData('id');

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            $value = <<<HTML
<div class="walmart-sku">
    {$value}&nbsp;&nbsp;
    <a href="#" class="walmart-sku-edit"
       onclick="ListingGridHandlerObj.editChannelDataHandler.showEditSkuPopup({$productId})">(edit)</a>
</div>
HTML;
        }

        if ($row->getData('is_online_price_invalid')) {
            $message = <<<HTML
Item Price violates Walmart pricing rules. Please adjust the Item Price to comply with the Walmart requirements.<br>
Once the changes are applied, Walmart Item will become Active automatically.
HTML;
            $msg = '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            if (empty($msg)) {
                return $value;
            }

            $value .= <<<HTML
<span style="float:right;">
    <img id="map_link_defected_message_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none; max-width: 400px;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</span>
HTML;
        }

        return $value;
    }

    public function callbackColumnGtin($gtin, $row, $column, $isExport)
    {
        if (empty($gtin)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productId = $row->getData('id');
        $gtinHtml = Mage::helper('M2ePro')->escapeHtml($gtin);

        $walmartHelper = Mage::helper('M2ePro/Component_Walmart');
        $marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
        $channelUrl = $walmartHelper->getItemUrl(
            $row->getData($walmartHelper->getIdentifierForItemUrl($marketplaceId)),
            $marketplaceId
        );

        if (!empty($channelUrl)) {
            $gtinHtml = <<<HTML
<a href="{$channelUrl}" target="_blank">{$gtin}</a>
HTML;
        }

        $html = '<div class="walmart-identifiers-gtin">'.$gtinHtml;

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            $html .= <<<HTML
&nbsp;&nbsp;<a href="#" class="walmart-identifiers-gtin-edit"
   onclick="ListingGridHandlerObj.editChannelDataHandler.showIdentifiersPopup('$productId')">(edit)</a>
HTML;
        }

        $html .= '</div>';

        $identifiers = array(
            'UPC'        => $row->getData('upc'),
            'EAN'        => $row->getData('ean'),
            'ISBN'       => $row->getData('isbn'),
            'Walmart ID' => $row->getData('wpid'),
            'Item ID'    => $row->getData('item_id')
        );

        $htmlAdditional = '';
        foreach ($identifiers as $title => $value) {
            if (empty($value)) {
                continue;
            }

            if (($row->getData('upc') || $row->getData('ean') || $row->getData('isbn')) &&
                ($row->getData('wpid') || $row->getData('item_id')) && $title == 'Walmart ID')
            {
                $htmlAdditional .= "<div class='separator-line'></div>";
            }

            $identifierCode  = Mage::helper('M2ePro')->__($title);
            $identifierValue = Mage::helper('M2ePro')->escapeHtml($value);

            $htmlAdditional .= <<<HTML
<div>
    <span style="display: inline-block; float: left;">
        <strong>{$identifierCode}:</strong>&nbsp;&nbsp;&nbsp;&nbsp;
    </span>
    <span style="display: inline-block; float: right;">
        {$identifierValue}
    </span>
    <div style="clear: both;"></div>
</div>
HTML;
        }

        if ($htmlAdditional != '') {
            $html .= <<<HTML
<span style="float:right;">
    <img class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
        <div class="walmart-identifiers">
            {$htmlAdditional}
        </div>
    </span>
</span>
HTML;
        }

        if (empty($html)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $html;
    }

    public function callbackColumnAvailableQty($qty, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($qty === null || $qty === '' ||
            ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
             !$row->getData('is_online_price_invalid')))
        {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($qty <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $qty;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlinePrice  = $row->getData('online_price');

        if ($onlinePrice === null || $onlinePrice === '' ||
            ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
             !$row->getData('is_online_price_invalid')))
        {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $marketplaceId)
            ->getChildObject()
            ->getDefaultCurrency();

        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlinePrice);
        }

        return $priceValue;
    }

    // ---------------------------------------

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId = (int)$row->getData('id');

        $html = $this->getViewLogIconHtml($row);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $listingProductId);

        $synchNote = $listingProduct->getSetting('additional_data', 'synch_template_list_rules_note');
        if (!empty($synchNote)) {
            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage($synchNote);

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
            } else {
                $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html .= '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html .= '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html .= '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Walmart')
            ->getObject('Listing_Product', $listingProductId);

        $statusChangeReasons = $listingProduct->getChildObject()->getStatusChangeReasons();

        $html .= $this->getStatusChangeReasons($statusChangeReasons);

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
            !$row->getData('is_online_price_invalid')) {
            $html .= <<<HTML
<br/>
<span style="color: gray">[Can be fixed]</span>
HTML;
        }

        return $html .
               $this->getScheduledTag($row) .
               $this->getLockedTag($row);
    }

    protected function getScheduledTag($row)
    {
        $html = '';

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row->getData('id'));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();
        if (!$scheduledAction->getId()) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator'])) {
                    $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isPromotionsAllowed()) {
                            $reviseParts[] = 'Promotions';
                        }

                        if ($configurator->isDetailsAllowed()) {
                            $params = $additionalData['params'];

                            if (isset($params['changed_sku'])) {
                                $reviseParts[] = 'SKU';
                            }

                            if (isset($params['changed_identifier'])) {
                                $reviseParts[] = strtoupper($params['changed_identifier']['type']);
                            }

                            $reviseParts[] = 'Details';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $html .= '<br/><span style="color: #605fff">[Revise of '.
                        implode(', ', $reviseParts).' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Retire is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    protected function getLockedTag($row)
    {
        $html = '';

        $tempLocks = $this->getLockedData($row);
        foreach ($tempLocks['object_locks'] as $lock) {
            switch ($lock->getTag()) {
                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[Listing...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relisting...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revising...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stopping...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stopping...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Removing...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->_lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load(
                $row->getData('id')
            )->getProcessingLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action'    => !empty($objectLocks),
            );
            $this->_lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->_lockedDataCache[$listingProductId];
    }

    // ---------------------------------------

    public function callbackProductOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_product_options":[^}]*' .
                        $value['attr'] . '[[:space:]]*":"[[:space:]]*' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value']).'[[:space:]]*'))
                );
            }
        }
    }

    public function callbackChannelOptions($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && isset($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"variation_channel_options":[^}]*' .
                        $value['attr'] . '[[:space:]]*":"[[:space:]]*' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value']).'[[:space:]]*'))
                );
            }
        }
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_qty >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'online_qty <= ' . (int)$value['to'];
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) || isset($value['to'])) {
            if (isset($value['from']) && $value['from'] != '') {
                $condition = 'second_table.online_price >= \'' . (float)$value['from'] . '\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'second_table.online_price <= \'' . (float)$value['to'] . '\'';
            }
        }

        $collection->getSelect()->where($condition);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null || $index == null) {
            return;
        }

        if (is_array($value) && isset($value['value']) || is_string($value)) {
            if (is_string($value)) {
                $status = (int)$value;
            } else {
                $status = (int)$value['value'];
            }

            $collection->addFieldToFilter($index, $status);
        }

        if (is_array($value) && isset($value['is_reset'])) {
            $collection->addFieldToFilter($index, Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
                       ->addFieldToFilter('is_online_price_invalid', 0);
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return string
     */
    public function getViewLogIconHtml($listingProduct)
    {
        $listingProductId = (int)$listingProduct->getId();

        // Get last messages
        // ---------------------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_product_id` = ?', $listingProductId)
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $connRead->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {
            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (!empty($tempActionRows)) {
                    $actionsRows[] = array(
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }

                $lastActionId = $row['action_id'];
            }

            $tempActionRows[] = $row;
        }

        if (!empty($tempActionRows)) {
            $actionsRows[] = array(
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (empty($actionsRows)) {
            return '';
        }

        foreach ($actionsRows as &$actionsRow) {
            usort(
                $actionsRow['items'], function($a, $b)
                {
                $sortOrder = array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 1,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 2,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 3,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
                }
            );
        }

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => (int)$listingProduct->getId(),
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'ListingGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'ListingGridHandlerObj.hideItemHelp',
            )
        );

        return $summary->toHtml();
    }

    protected function getAvailableActions()
    {
        return array(
            Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT,
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE
        );
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('List');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Retire on Channel');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Retire on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Reset Inactive (Blocked) Item');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            }

            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html.= $this->getAddNewChildButtonsHtml();
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }

        return $html;
    }

    public function getMassactionBlockHtml()
    {
        if ($this->isNewChildAllowed()) {
            $blockName = 'adminhtml_walmart_listing_variation_product_manage_tabs_variations_child_form';
            $form = $this->getLayout()->createBlock('M2ePro/'.$blockName);
            $form->setListingProductId($this->getListingProductId());

            return $form->toHtml() . parent::getMassactionBlockHtml();
        }

        return parent::getMassactionBlockHtml();
    }

    protected function getAddNewChildButtonsHtml()
    {
        if ($this->isNewChildAllowed()) {
            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('adminhtml')->__('Add New Child Product'),
                'onclick' => 'ListingGridHandlerObj.showNewChildForm()',
                'class'   => 'task',
                'id'      => 'add_new_child_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('add_new_child_button', $buttonBlock);
            // ---------------------------------------
        }

        return $this->getChildHtml('add_new_child_button');
    }

    protected function isNewChildAllowed()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->getListingProduct()->getChildObject();

        if (!$walmartListingProduct->getVariationManager()->getTypeModel()->hasMatchedAttributes()) {
            return false;
        }

        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        if ($this->hasChildWithEmptyProductOptions()) {
            return false;
        }

        return true;
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelVariations();
    }

    public function hasUnusedProductVariation()
    {
        return (bool)$this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedProductOptions();
    }

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    public function getUsedChannelVariations()
    {
        return (bool)$this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUsedChannelOptions();
    }

    // ---------------------------------------

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/viewVariationsGridAjax', array(
            '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $listingId = $this->getListingProduct()->getListingId();

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        // ---------------------------------------
        $urls = array();

        $path = 'adminhtml_walmart_log/listingProduct';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'channel' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_walmart_listing/view', array('id' => $listingId))
            )
        );

        $urls['adminhtml_walmart_listing/getEditSkuPopup'] = $this->getUrl(
            '*/adminhtml_walmart_listing/getEditSkuPopup'
        );
        $urls['adminhtml_walmart_listing/editSku'] = $this->getUrl(
            '*/adminhtml_walmart_listing/editSku'
        );
        $urls['adminhtml_walmart_listing/getEditIdentifiersPopup'] = $this->getUrl(
            '*/adminhtml_walmart_listing/getEditIdentifiersPopup'
        );
        $urls['adminhtml_walmart_listing/editIdentifier'] = $this->getUrl(
            '*/adminhtml_walmart_listing/editIdentifier'
        );

        $urls['adminhtml_walmart_listing/runResetProducts'] = $this->getUrl(
            '*/adminhtml_walmart_listing/runResetProducts'
        );

        $urls['adminhtml_walmart_listing_variation_product_manage/createNewChild'] = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/createNewChild'
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Walmart::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_walmart_log/listing', array(
            'id' =>$listingId,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_walmart_listing/view', array('id' => $listingId))
            )
        );

        $checkLockListing = $this->getUrl('*/adminhtml_listing/checkLockListing', array('component' => $component));
        $lockListingNow = $this->getUrl('*/adminhtml_listing/lockListingNow', array('component' => $component));
        $unlockListingNow = $this->getUrl('*/adminhtml_listing/unlockListingNow', array('component' => $component));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_walmart_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_walmart_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_walmart_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_walmart_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_walmart_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_walmart_listing/runDeleteAndRemoveProducts');
        $runResetProducts = $this->getUrl('*/adminhtml_walmart_listing/runResetProducts');

        $setChildListingProductOptions = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/setChildListingProductOptions'
        );

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task was successfully submitted to be processed.')
        );
        $taskRealtimeCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task was completed successfully.')
        );
        $taskCompletedWarningMessage = $helper->escapeJs(
            $helper->__(
                '"%task_title%" Task was completed with warnings.
            <a target="_blank" href="%url%">View Log</a> for the details.'
            )
        );
        $taskCompletedErrorMessage = $helper->escapeJs(
            $helper->__(
                '"%task_title%" Task was completed with errors. 
            <a target="_blank" href="%url%">View Log</a> for the details.'
            )
        );

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Walmart request(s) are being processed now.'));
        $sendingDataToWalmartMessage = $helper->escapeJs(
            $helper->__(
                'Sending %product_title% Product(s) data on Walmart.'
            )
        );
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing All Items On Walmart')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing Selected Items On Walmart')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Revising Selected Items On Walmart')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Walmart')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Walmart')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(
                Mage::helper('M2ePro')
                ->__('Stopping On Walmart And Removing From Listing Selected Items')
            );
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(
                Mage::helper('M2ePro')
                ->__('Retiring From Walmart And Removing from Listing Selected Items')
            );
        $resetBlockedProductsMessage = Mage::helper('M2ePro')
            ->escapeJs(
                Mage::helper('M2ePro')
                ->__('Reset Inactive (Blocked) Items')
            );

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select Items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $errorChangingProductOptions = $helper->escapeJs($helper->__('Please Select Product Options.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));
        $confirmWord = $helper->escapeJs($helper->__('Confirm'));
        $cancelWord = $helper->escapeJs($helper->__('Cancel'));

        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $mapToTemplateDescription = $this->getUrl('*/adminhtml_walmart_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_walmart_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_walmart_listing/validateProductsForTemplateDescriptionAssign'
        );
        $viewTemplateDescriptionsGrid = $this->getUrl('*/adminhtml_walmart_listing/viewTemplateDescriptionsGrid');
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy for Products'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Edit SKU'         => $helper->__('Edit SKU'),
            'Edit Identifiers' => $helper->__('Edit Identifiers'),

            'Updating SKU has successfully submitted to be processed.'  =>
                $helper->__('Updating SKU has successfully submitted to be processed.'),
            'Updating GTIN has successfully submitted to be processed.' =>
                $helper->__('Updating GTIN has successfully submitted to be processed.'),
            'Updating UPC has successfully submitted to be processed.'  =>
                $helper->__('Updating UPC has successfully submitted to be processed.'),
            'Updating EAN has successfully submitted to be processed.'  =>
                $helper->__('Updating EAN has successfully submitted to be processed.'),
            'Updating ISBN has successfully submitted to be processed.' =>
                $helper->__('Updating ISBN has successfully submitted to be processed.'),

            'Required at least one identifier' => $helper->__('Required at least one identifier'),

            'SKU contains the special characters that are not allowed by Walmart.' => $helper->__(
                'Hyphen (-), space ( ), and period (.) are not allowed by Walmart. Please use a correct format.'
            )
            )
        );

        $javascriptMain = <<<HTML
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.url.logViewUrl = '{$logViewUrl}';

    M2ePro.url.checkLockListing = '{$checkLockListing}';
    M2ePro.url.lockListingNow = '{$lockListingNow}';
    M2ePro.url.unlockListingNow = '{$unlockListingNow}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.runDeleteAndRemoveProducts = '{$runDeleteAndRemoveProducts}';
    M2ePro.url.runResetProducts = '{$runResetProducts}';
    M2ePro.url.setChildListingProductOptions = '{$setChildListingProductOptions}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_realtime_completed_success_message = '{$taskRealtimeCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToWalmartMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.deleting_and_removing_selected_items_message = '{$deletingAndRemovingSelectedItemsMessage}';
    M2ePro.text.reset_blocked_products_message = '{$resetBlockedProductsMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.error_changing_product_options = '{$errorChangingProductOptions}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';
    M2ePro.text.confirm_word = '{$confirmWord}';
    M2ePro.text.cancel_word = '{$cancelWord}';

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';

    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = 'walmartVariationProductManageGrid';

    // fix for tool tip position in iframe
    MagentoFieldTip.prototype.changeToolTipPosition = function(element)
    {
        var toolTip = element.up().select('.tool-tip-message')[0];

        var settings = {
            setHeight: false,
            setWidth: false,
            setLeft: true,
            offsetTop: 25,
            offsetLeft: -20
        };

        if (element.up().getStyle('float') == 'right') {
            settings.offsetLeft += 18;
        }
        if (element.up().match('span')) {
            settings.offsetLeft += 15;
        }

        toolTip.clonePosition(element, settings);

        if (toolTip.hasClassName('tip-left')) {
            toolTip.style.left = (parseInt(toolTip.style.left) - toolTip.getWidth() - 10) + 'px';
        }
    };

    Event.observe(window, 'load', function() {

        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        ListingGridHandlerObj = new WalmartListingVariationProductManageVariationsGridHandler(
            'walmartVariationProductManageGrid',
            {$listingId}
        );

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
        }, 350);
    });

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

</script>
HTML;

        $additionalCss = <<<HTML
<style>
    body {
        background: none;
    }

    .wrapper {
        min-width: inherit;
    }

    .footer {
        display: none;
    }

    .middle {
        padding: 0px;
        background: none;
    }

    td.help_line .hl_close {
        margin-top: -6px;
    }

    td.help_line .hl_header {
        padding: 0 0 10px !important;
    }

</style>
HTML;

        return  '<div id="messages"></div>' .
                '<div id="listing_view_progress_bar"></div>' .
                '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
                '<div id="listing_view_content_container">'.
                    parent::_toHtml() .
                '</div>' . $javascriptMain . $additionalCss;
    }

    //########################################

    protected function canChangeProductVariation(Ess_M2ePro_Model_Listing_Product $childListingProduct)
    {
        if (!$this->hasUnusedProductVariation()) {
            return false;
        }

        $lockData = $this->getLockedData($childListingProduct);
        if ($lockData['in_action']) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartChildListingProduct */
        $walmartChildListingProduct = $childListingProduct->getChildObject();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $walmartChildListingProduct->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched()) {
            return false;
        }

        if (!$childTypeModel->getParentTypeModel()->hasMatchedAttributes()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getTemplateDescriptionLinkHtml($listingProduct)
    {
        $templateDescriptionEditUrl = $this->getUrl(
            '*/adminhtml_walmart_template_description/edit', array(
            'id' => $listingProduct->getChildObject()->getTemplateDescriptionId()
            )
        );

        $helper = Mage::helper('M2ePro');
        $templateTitle = $listingProduct->getChildObject()->getDescriptionTemplate()->getTitle();

        return <<<HTML
<span style="font-size: 9px;">{$helper->__('Description Title')}:&nbsp;
    <a target="_blank" href="{$templateDescriptionEditUrl}">
        {$helper->escapeHtml($templateTitle)}</a>
</span>
<br/>
HTML;
    }

    protected function getStatusChangeReasons($statusChangeReasons)
    {
        if (empty($statusChangeReasons)) {
            return '';
        }

        $html = '<li style="margin-bottom: 5px;">'
            . implode('</li><li style="margin-bottom: 5px;">', $statusChangeReasons)
            . '</li>';

        return <<<HTML
        <div style="display: inline-block; width: 16px; margin-left: 3px; margin-right: 4px;">
            <img class="tool-tip-image"
                 style="vertical-align: middle;"
                 src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
            <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
                <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
                <ul>
                    {$html}
                </ul>
            </span>
        </div>
HTML;
    }

    //########################################

    public function getProductVariationsTree($childProduct, $attributes)
    {
        $unusedVariations = $this->getUnusedProductVariations();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $childProduct->getChildObject()->getVariationManager()->getTypeModel();

        if ($childTypeModel->isVariationProductMatched()) {
            $unusedVariations[] = $childTypeModel->getProductOptions();
        }

        $variationsSets = $this->getAttributesVariationsSets($unusedVariations);
        $variationsSetsSorted =  array();

        foreach ($attributes as $attribute) {
            $variationsSetsSorted[$attribute] = $variationsSets[$attribute];
        }

        $firstAttribute = key($variationsSetsSorted);

        return $this->prepareVariations($firstAttribute, $unusedVariations, $variationsSetsSorted);
    }

    protected function prepareVariations($currentAttribute,$unusedVariations,$variationsSets,$filters = array())
    {
        $return = false;

        $temp = array_flip(array_keys($variationsSets));

        $lastAttributePosition = count($variationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {
            $temp = array_keys($variationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($variationsSets[$currentAttribute] as $option) {
                $filters[$currentAttribute] = $option;

                $result = $this->prepareVariations(
                    $nextAttribute, $unusedVariations, $variationsSets, $filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$option] = $result;
            }

            if ($return !== false) {
                ksort($return[$currentAttribute]);
            }

            return $return;
        }

        $return = false;
        foreach ($unusedVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $attribute => $option) {
                if ($attribute == $currentAttribute) {
                    if (count($variationsSets) != 1) {
                        continue;
                    }

                    $values = array_flip($variationsSets[$currentAttribute]);
                    $return = array($currentAttribute => $values);

                    foreach ($return[$currentAttribute] as &$option) {
                        $option = true;
                    }

                    return $return;
                }

                if ($option != $filters[$attribute]) {
                    unset($unusedVariations[$key]);
                    continue;
                }

                foreach ($magentoVariation as $tempAttribute => $tempOption) {
                    if ($tempAttribute == $currentAttribute) {
                        $option = $tempOption;
                        $return[$currentAttribute][$option] = true;
                    }
                }
            }
        }

        if (count($unusedVariations) < 1) {
            return false;
        }

        if ($return !== false) {
            ksort($return[$currentAttribute]);
        }

        return $return;
    }

    //########################################

    public function getCurrentProductVariations()
    {

        if ($this->_currentProductVariations !== null) {
            return $this->_currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->_currentProductVariations = $productVariations;
    }

    public function getUsedProductVariations()
    {
        if ($this->_usedProductVariations === null) {
            $this->_usedProductVariations = $this->getListingProduct()
                                                 ->getChildObject()
                                                 ->getVariationManager()
                                                 ->getTypeModel()
                                                 ->getUsedProductOptions();
        }

        return $this->_usedProductVariations;
    }

    //########################################

    public function getUnusedProductVariations()
    {
        return $this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedProductOptions();
    }

    //########################################

    public function getChildListingProducts()
    {
        if ($this->_childListingProducts !== null) {
            return $this->_childListingProducts;
        }

        return $this->_childListingProducts = $this->getListingProduct()->getChildObject()
                                                   ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getAttributesVariationsSets($variations)
    {
        $attributesOptions = array();

        foreach ($variations as $variation) {
            foreach ($variation as $attr => $option) {
                if (!isset($attributesOptions[$attr])) {
                    $attributesOptions[$attr] = array();
                }

                if (!in_array($option, $attributesOptions[$attr])) {
                    $attributesOptions[$attr][] = $option;
                }
            }
        }

        return $attributesOptions;
    }

    //########################################

    protected function parseGroupedData($data)
    {
        $result = array();

        if (empty($data)) {
            return $result;
        }

        $variationData = explode('||', $data);
        foreach ($variationData as $variationAttribute) {
            $value = explode('==', $variationAttribute);
            $result[$value[0]] = $value[1];
        }

        return $result;
    }

    //########################################
}
