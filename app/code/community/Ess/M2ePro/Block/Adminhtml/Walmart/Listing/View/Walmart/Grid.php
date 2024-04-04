<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Walmart_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Walmart_Template_SellingFormat */
    protected $_sellingFormatTemplate = null;

    protected $_lockedDataCache = array();

    protected $_childProductsWarningsData;

    protected $_hideSwitchToIndividualConfirm;
    protected $_hideSwitchToParentConfirm;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    protected $_parentAndChildReviseScheduledCache = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->_hideSwitchToIndividualConfirm = $this->_listing->getSetting(
            'additional_data',
            'hide_switch_to_individual_confirm', 0
        );

        $this->_hideSwitchToParentConfirm = $this->_listing->getSetting(
            'additional_data',
            'hide_switch_to_parent_confirm', 0
        );

        $this->setId('walmartListingViewGrid' . $this->_listing->getId());

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Template_SellingFormat', $this->_listing->getData('template_selling_format_id'), null,
            array('template')
        );
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_view_modeSwitcher'
        );
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->setListingProductModeOn();
        $collection->setStoreId($this->_listing->getStoreId());
        $collection->setListing($this->_listing->getId());

        $collection->addAttributeToSelect('name')
                   ->addAttributeToSelect('sku')
                   ->joinStockItem();

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'component_mode' => 'component_mode',
                'status' => 'status',
                'additional_data' => 'additional_data',
                'listing_id' => 'listing_id',
            ),
            array(
                'listing_id' => (int)$this->_listing->getId()
            )
        );
        $collection->joinTable(
            array('wlp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'variation_child_statuses' => 'variation_child_statuses',
                'walmart_sku' => 'sku',
                'gtin' => 'gtin',
                'upc' => 'upc',
                'ean' => 'ean',
                'isbn' => 'isbn',
                'wpid' => 'wpid',
                'item_id' => 'item_id',
                'online_qty' => 'online_qty',
                'online_price' => 'online_price',
                'is_variation_parent' => 'is_variation_parent',
                'is_online_price_invalid' => 'is_online_price_invalid',
                'online_start_date' => 'online_start_date',
                'online_end_date' => 'online_end_date',
                'status_change_reasons' => 'status_change_reasons',
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->joinTable(
            array('wl' => 'M2ePro/Walmart_Listing'),
            'listing_id=listing_id',
            array(
                'template_selling_format_id' => 'template_selling_format_id'
            )
        );
        $collection->joinTable(
            array('wtsf' => 'M2ePro/Walmart_Template_SellingFormat'),
            'template_selling_format_id = template_selling_format_id',
            array(
                'is_set_online_promotions'
                => new \Zend_Db_Expr('wtsf.promotions_mode = 1 AND wlp.online_promotions IS NOT NULL')
            )
        );

        if ($this->isFilterOrSortByPriceIsUsed('online_price', 'walmart_online_price')) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('lps' => Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction')->getMainTable()),
            'lps.listing_product_id=main_table.id',
            array()
        );

        $collection->addFieldToFilter('is_variation_parent', 0);
        $collection->addFieldToFilter(
            'variation_parent_id', array('in' => $this->getCollection()->getColumnValues('id'))
        );
        $collection->addFieldToFilter('lps.action_type', Ess_M2ePro_Model_Listing_Product::ACTION_REVISE);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'variation_parent_id' => 'second_table.variation_parent_id',
                'count'               => new Zend_Db_Expr('COUNT(lps.id)')
            )
        );
        $collection->getSelect()->group('variation_parent_id');

        foreach ($collection->getItems() as $item) {
            $this->_parentAndChildReviseScheduledCache[$item->getData('variation_parent_id')] = true;
        }

        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'       => Mage::helper('M2ePro')->__('Product ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id'     => $this->_listing->getStoreId(),
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'                    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'name',
                'filter_index'              => 'name',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'sku', array(
                'header'         => Mage::helper('M2ePro')->__('SKU'),
                'align'          => 'left',
                'width'          => '225px',
                'type'           => 'text',
                'index'          => 'walmart_sku',
                'filter_index'   => 'walmart_sku',
                'renderer'       => 'M2ePro/adminhtml_walmart_grid_column_renderer_sku'
            )
        );

        $this->addColumn(
            'gtin', array(
                'header'                    => Mage::helper('M2ePro')->__('GTIN'),
                'align'                     => 'left',
                'width'                     => '200px',
                'type'                      => 'text',
                'index'                     => 'gtin',
                'filter_index'              => 'gtin',
                'marketplace_id'            => $this->_listing->getMarketplaceId(),
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_gtin',
                'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '150px',
                'type'                      => 'number',
                'index'                     => 'online_qty',
                'filter_index'              => 'online_qty',
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

        if ($dir == 'desc') {
            $priceSortField = 'max_online_price';
        } else {
            $priceSortField = 'min_online_price';
        }

        $this->addColumn(
            'online_price', array(
                'header'                    => Mage::helper('M2ePro')->__('Price'),
                'align'                     => 'right',
                'width'                     => '150px',
                'type'                      => 'number',
                'index'                     => $priceSortField,
                'filter_index'              => $priceSortField,
                'frame_callback'            => array($this, 'callbackColumnPrice'),
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '170px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Incomplete')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $isResetFilterShouldBeShown = Mage::helper('M2ePro/View_Walmart')->isResetFilterShouldBeShown(
            'listing_id',
            $this->_listing->getId()
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
            'actions'            => Mage::helper('M2ePro')->__('Actions'),
            'other'              => Mage::helper('M2ePro')->__('Other'),
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
                'label'    => Mage::helper('M2ePro')->__('Reset Incomplete Item(s)'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        $productTitle = $helper->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>' . $helper->__('SKU') . ':</strong> ' . $helper->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $gtin = $row->getData('gtin');

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
        $parentType = $variationManager->getTypeModel();

        if ($variationManager->isRelationParentType()) {
            $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();
            $virtualProductAttributes = $variationManager->getTypeModel()->getVirtualProductAttributes();
            $virtualChannelAttributes = $variationManager->getTypeModel()->getVirtualChannelAttributes();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
            $attributesStr = '';
            if (empty($virtualProductAttributes) && empty($virtualChannelAttributes)) {
                $attributesStr = implode(', ', $productAttributes);
            } else {
                foreach ($productAttributes as $attribute) {
                    if (in_array($attribute, array_keys($virtualProductAttributes))) {
                        $attributesStr .= '<span style="border-bottom: 2px dotted grey">' . $attribute .
                            ' (' . $virtualProductAttributes[$attribute] . ')</span>, ';
                    } else if (in_array($attribute, array_keys($virtualChannelAttributes))) {
                        $attributesStr .= '<span>' . $attribute .
                            ' (' . $virtualChannelAttributes[$attribute] . ')</span>, ';
                    } else {
                        $attributesStr .= $attribute . ', ';
                    }
                }

                $attributesStr = rtrim($attributesStr, ', ');
            }

            $value .= $attributesStr;

            if (!$parentType->hasChannelGroupId() &&
                !$listingProduct->isSetProcessingLock('child_products_in_action')) {
                $popupTitle = $helper->escapeJs(
                    $helper->escapeHtml(
                        $helper->__('Manage Magento Product Variations')
                    )
                );

                $linkTitle = $helper->escapeJs(
                    $helper->escapeHtml(
                        $helper->__('Change "Magento Variations" Mode')
                    )
                );

                $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

                $switchToIndividualJsMethod = <<<JS
ListingProductVariationObj
    .setListingProductId({$listingProductId})
        .showSwitchToIndividualModePopUp('{$popupTitle}');
JS;

                if ($this->_hideSwitchToIndividualConfirm) {
                    $switchToIndividualJsMethod = <<<JS
ListingProductVariationObj
    .setListingProductId({$listingProductId})
        .showManagePopup('{$popupTitle}');
JS;
                }

                $value .= <<<HTML
&nbsp;
<a  href="javascript:"
    onclick="{$switchToIndividualJsMethod}"
    title="{$linkTitle}">
<img width="12" height="12" style="height: 12px; width: 12px; vertical-align: middle;" src="{$iconSettingsPath}"></a>
HTML;
            }

            $value .= '</div>';

            $linkContent = $helper->__('Manage Variations');
            $vpmt = $helper->escapeJs(
                $helper->__('Manage Variations of "%s" ', $productTitle)
            );

            if (!empty($gtin)) {
                $vpmt .= '('. $gtin .')';
            }

            $problemStyle = '';
            $problemIcon = '';

            $linkTitle = $helper->__('Open Manage Variations Tool');

            if (!$parentType->hasMatchedAttributes() || !$parentType->hasChannelAttributes()) {
                $linkTitle = $helper->__('Action Required');
                $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                $problemIcon = '<img style="vertical-align: middle;" src="'
                    . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
            } elseif ($this->hasChildWithWarning($listingProductId)) {
                $linkTitle = $helper->__('Action Required');
                $problemStyle = 'style="font-weight: bold;" ';
                $iconPath = $this->getSkinUrl('M2ePro/images/warning.png');
                $problemIcon = '<img style="vertical-align: middle;" src="'
                    . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
            }

                $value .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
    <a {$problemStyle}href="javascript:"
    onclick="ListingGridObj.variationProductManageHandler.openPopUp(
            {$listingProductId},'{$helper->escapeHtml($vpmt)}'
        )"
    title="{$linkTitle}">{$linkContent}</a>&nbsp;{$problemIcon}
</div>
HTML;

            if ($childListingProductIds = $this->getRequest()->getParam('child_listing_product_ids')) {
                $value .= <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        ListingGridObj.variationProductManageHandler.openPopUp(
                {$listingProductId}, '{$vpmt}', 'searched_by_child', '{$childListingProductIds}'
            )
    });

</script>
HTML;
            }

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $value .= '<strong>' . $helper->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . $helper->escapeHtml($option) . '<br/>';
            }

            $value .= '</div>';
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if (!$hasInActionLock) {
            $popupTitle = $helper->__('Manage Magento Product Variation');
            $linkTitle  = $helper->__('Edit Variation');
            $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/pencil.png').'">';

            $value .= <<<HTML
<div style="clear: both"></div>
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationObj
            .setListingProductId({$listingProductId})
            .showEditPopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;
        }

        $popupTitle = $helper->__('Manage Magento Product Variations');
        $linkTitle  = $helper->__('Add Another Variation(s)');
        $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/add.png').'">';

        $value.= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

        if (empty($gtin)) {
            $linkTitle = $helper->escapeJs(
                $helper->escapeHtml(
                    $helper->__('Change "Magento Variations" Mode')
                )
            );

            $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

            $switchToParentJsMethod = <<<JS
ListingProductVariationObj
    .setListingProductId({$listingProductId})
        .showSwitchToParentModePopUp('{$popupTitle}');
JS;

            if ($this->_hideSwitchToParentConfirm) {
                $switchToParentJsMethod = <<<JS
ListingProductVariationObj
    .setListingProductId({$listingProductId})
        .resetListingProductVariation();
JS;
            }

            $value .= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a href="javascript:"
        onclick="{$switchToParentJsMethod}"
        title="{$linkTitle}">
    <img width="12" height="12" src="{$iconSettingsPath}"></a>
</div>
HTML;
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ((!$row->getData('is_variation_parent') &&
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlineMinPrice = (float)$row->getData('min_online_price');
        $onlineMaxPrice = (float)$row->getData('max_online_price');

        if (empty($onlineMinPrice)){
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent') ||
                ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
                    !$row->getData('is_online_price_invalid'))
            ) {
                return Mage::helper('M2ePro')->__('N/A');
            } else {
                return '<i style="color:gray;">receiving...</i>';
            }
        }

        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $this->_listing->getMarketplaceId())
            ->getChildObject()
            ->getDefaultCurrency();

        if ($row->getData('is_variation_parent')) {
            $onlinePriceStr = '<span style="color: #f00;">0</span>';
            if (!empty($onlineMinPrice) && !empty($onlineMaxPrice)) {
                $onlineMinPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMinPrice);
                $onlineMaxPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMaxPrice);

                $onlinePriceStr = $onlineMinPriceStr
                                         .(($onlineMinPrice != $onlineMaxPrice)?' - '
                                         .$onlineMaxPriceStr:'');
            }

            return $onlinePriceStr;
        }

        $onlinePrice = $row->getData('online_price');
        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlinePrice);
        }

        $resultHtml = '';

        if (empty($resultHtml)) {
            $resultHtml = $priceValue;
        }

        $isSetOnlinePromotions = (bool)$row->getData('is_set_online_promotions');
        if ($isSetOnlinePromotions) {
            $promotionTooltipHtml = $this->getTooltipHtml(
                $this->getSkinUrl('M2ePro/images/promotions.svg'),
                $this->__('Price without promotions<br>Actual price is available on Walmart.')
            );

            $resultHtml = $promotionTooltipHtml. '&nbsp;' . $resultHtml;
        }

        return $resultHtml;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_Status $viewLogIcon */
        $status = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_grid_column_renderer_status');
        $status->setParentAndChildReviseScheduledCache($this->_parentAndChildReviseScheduledCache);

        return $status->render($row);
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterGtin($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = <<<SQL
wlp.gtin LIKE '%{$value}%' OR
wlp.upc LIKE '%{$value}%' OR
wlp.ean LIKE '%{$value}%' OR
wlp.isbn LIKE '%{$value}%' OR
wlp.wpid LIKE '%{$value}%' OR
wlp.item_id LIKE '%{$value}%'
SQL;

        $collection->getSelect()->where($where);
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
                $condition = 'min_online_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'min_online_price <= \''.(float)$value['to'].'\'';
            }

            $condition = '(' . $condition . ') OR (';

            if (isset($value['from']) && $value['from'] != '') {
                $condition .= 'max_online_price >= \''.(float)$value['from'].'\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'max_online_price <= \''.(float)$value['to'].'\'';
            }

            $condition .= ')';
        }

        $collection->getSelect()->having($condition);
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

            $collection->getSelect()->where(
                "lp.status = {$status} OR
                (wlp.variation_child_statuses REGEXP '\"{$status}\":[^0]' AND wlp.is_variation_parent = 1)"
            );
        }

        if (is_array($value) && isset($value['is_reset'])) {
            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $childProducts */
            $collectionVariationParent = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $collectionVariationParent->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);
            $collectionVariationParent->addFieldToFilter('variation_parent_id', array('notnull' => true));
            $collectionVariationParent->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collectionVariationParent->getSelect()->columns(array('second_table.variation_parent_id'));

            $variationParentIds = $collectionVariationParent->getColumnValues('variation_parent_id');

            $collection->addFieldToFilter(
                array(
                    array('attribute' => $index, 'eq' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED),
                    array('attribute' => 'id', 'in' => $variationParentIds)
                )
            )->addFieldToFilter('is_online_price_invalid', 0);
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_walmart_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->_lockedDataCache[$listingProductId])) {
            $objectLocks = Mage::getModel('M2ePro/Listing_Product')->load(
                $listingProductId
            )->getProcessingLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action'    => !empty($objectLocks),
            );
            $this->_lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->_lockedDataCache[$listingProductId];
    }

    //########################################

    protected function getChildProductsWarningsData()
    {
        if ($this->_childProductsWarningsData === null) {
            $this->_childProductsWarningsData = array();

            $productsIds = array();
            foreach ($this->getCollection()->getItems() as $row) {
                $productsIds[] = $row['id'];
            }

            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableWalmartListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_walmart_listing_product');

            $select = $connRead->select();
            $select->distinct(true);
            $select->from(array('wlp' => $tableWalmartListingProduct), array('variation_parent_id'))
                ->where('variation_parent_id IN (?)', $productsIds)
                ->where(
                    'is_variation_product_matched = 0'
                );

            $this->_childProductsWarningsData = Mage::getResourceModel('core/config')
                                                    ->getReadConnection()
                                                    ->fetchCol($select);
        }

        return $this->_childProductsWarningsData;
    }

    protected function hasChildWithWarning($listingProductId)
    {
        return in_array($listingProductId, $this->getChildProductsWarningsData());
    }

    private function getTooltipHtml($icon, $content)
    {
        return <<<TOOLTIP
<span>
    <img class="tool-tip-image" style="vertical-align:middle;height:14px" src="$icon">
    <span class="tool-tip-message tip-left" style="display:none;text-align: left;width: 300px;">
        <span style="color:gray;">$content</span>
    </span>
</span>
TOOLTIP;
    }

    //########################################
}
