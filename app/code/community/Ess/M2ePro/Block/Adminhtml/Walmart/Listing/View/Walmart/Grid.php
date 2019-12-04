<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_Processor as ListProcessor;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Walmart_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Walmart_Template_SellingFormat */
    protected $_sellingFormatTemplate = null;

    protected $_lockedDataCache = array();

    protected $_childProductsWarningsData;

    protected $_hideSwitchToIndividualConfirm;
    protected $_hideSwitchToParentConfirm;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $listing = Mage::helper('M2ePro/Component')->getUnknownObject('Listing', $listingData['id']);

        $this->_hideSwitchToIndividualConfirm = $listing->getSetting(
            'additional_data',
            'hide_switch_to_individual_confirm', 0
        );

        $this->_hideSwitchToParentConfirm = $listing->getSetting(
            'additional_data',
            'hide_switch_to_parent_confirm', 0
        );

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingViewWalmartGrid'.$listingData['id']);
        // ---------------------------------------

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Template_SellingFormat', $listingData['template_selling_format_id'], null,
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
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection
        // ---------------------------------------
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->setListingProductModeOn();
        $collection->setStoreId($listingData['store_id']);
        $collection->setListing($listingData['id']);

        if ($this->isFilterOrSortByPriceIsUsed('online_price', 'walmart_online_price')) {
            $collection->setIsNeedToUseIndexerParent(true);
        }

        $collection->addAttributeToSelect('name')
                   ->addAttributeToSelect('sku')
                   ->joinStockItem();

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'walmart_status'  => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('wlp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'variation_child_statuses'       => 'variation_child_statuses',
                'walmart_sku'                    => 'sku',
                'gtin'                           => 'gtin',
                'upc'                            => 'upc',
                'ean'                            => 'ean',
                'isbn'                           => 'isbn',
                'wpid'                           => 'wpid',
                'channel_url'                    => 'channel_url',
                'item_id'                        => 'item_id',
                'online_qty'                     => 'online_qty',
                'online_price'                   => 'online_price',
                'is_variation_parent'            => 'is_variation_parent',
                'is_details_data_changed'        => 'is_details_data_changed',
                'is_online_price_invalid'        => 'is_online_price_invalid',
                'online_start_date'              => 'online_start_date',
                'online_end_date'                => 'online_end_date',
                'status_change_reasons'          => 'status_change_reasons'
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();

        return $result;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnListingProductId')
            )
        );

        $this->addColumn(
            'name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '225px',
            'type' => 'text',
            'index' => 'walmart_sku',
            'filter_index' => 'walmart_sku',
            'frame_callback' => array($this, 'callbackColumnWalmartSku')
            )
        );

        $this->addColumn(
            'gtin', array(
            'header' => Mage::helper('M2ePro')->__('GTIN'),
            'align' => 'left',
            'width' => '200px',
            'type' => 'text',
            'index' => 'gtin',
            'filter_index' => 'gtin',
            'frame_callback' => array($this, 'callbackColumnGtin'),
            'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'online_qty', array(
            'header' => Mage::helper('M2ePro')->__('QTY'),
            'align' => 'right',
            'width' => '150px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty'),
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
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '150px',
            'type' => 'number',
            'index' => $priceSortField,
            'filter_index' => $priceSortField,
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $statusColumn = array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '170px',
            'index' => 'walmart_status',
            'filter_index' => 'walmart_status',
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

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if (Mage::helper('M2ePro/View_Walmart')->isResetFilterShouldBeShown($listingData['id'])) {
            $statusColumn['filter'] = 'M2ePro/adminhtml_walmart_grid_column_filter_status';
        }

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
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

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if (Mage::helper('M2ePro/View_Walmart')->isResetFilterShouldBeShown($listingData['id'])) {
            $this->getMassactionBlock()->addItem(
                'resetProducts', array(
                'label'    => Mage::helper('M2ePro')->__('Reset Inactive (Blocked) Item(s)'),
                'url'      => '',
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                ), 'other'
            );
        }

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

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
                $popupTitle = Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->escapeHtml(
                        Mage::helper('M2ePro')->__('Manage Magento Product Variations')
                    )
                );

                $linkTitle = Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->escapeHtml(
                        Mage::helper('M2ePro')->__('Change "Magento Variations" Mode')
                    )
                );

                $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

                $switchToIndividualJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .showSwitchToIndividualModePopUp('{$popupTitle}');
JS;

                if ($this->_hideSwitchToIndividualConfirm) {
                    $switchToIndividualJsMethod = <<<JS
ListingProductVariationHandlerObj
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

            $linkContent = Mage::helper('M2ePro')->__('Manage Variations');
            $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
            $vpmt = addslashes($vpmt);

            if (!empty($gtin)) {
                $vpmt .= '('. $gtin .')';
            }

            $problemStyle = '';
            $problemIcon = '';

            $linkTitle = Mage::helper('M2ePro')->__('Open Manage Variations Tool');

            if (!$parentType->hasMatchedAttributes() || !$parentType->hasChannelAttributes()) {
                $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                $problemIcon = '<img style="vertical-align: middle;" src="'
                    . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
            } elseif ($this->hasChildWithWarning($listingProductId)) {
                $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                $problemStyle = 'style="font-weight: bold;" ';
                $iconPath = $this->getSkinUrl('M2ePro/images/warning.png');
                $problemIcon = '<img style="vertical-align: middle;" src="'
                    . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
            }

                $value .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
    <a {$problemStyle}href="javascript:"
    onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}')"
    title="{$linkTitle}">{$linkContent}</a>&nbsp;{$problemIcon}
</div>
HTML;

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }

            $value .= '</div>';
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if (!$hasInActionLock) {
            $popupTitle = Mage::helper('M2ePro')->__('Manage Magento Product Variation');
            $linkTitle  = Mage::helper('M2ePro')->__('Edit Variation');
            $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/pencil.png').'">';

            $value .= <<<HTML
<div style="clear: both"></div>
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showEditPopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;
        }

        $popupTitle = Mage::helper('M2ePro')->__('Manage Magento Product Variations');
        $linkTitle  = Mage::helper('M2ePro')->__('Add Another Variation(s)');
        $linkContent = '<img width="12" height="12" src="'.$this->getSkinUrl('M2ePro/images/add.png').'">';

        $value.= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        onclick="ListingProductVariationHandlerObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}">{$linkContent}</a>
</div>
HTML;

        if (empty($gtin)) {
            $linkTitle = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->escapeHtml(
                    Mage::helper('M2ePro')->__('Change "Magento Variations" Mode')
                )
            );

            $iconSettingsPath = $this->getSkinUrl('M2ePro/images/settings.png');

            $switchToParentJsMethod = <<<JS
ListingProductVariationHandlerObj
    .setListingProductId({$listingProductId})
        .showSwitchToParentModePopUp('{$popupTitle}');
JS;

            if ($this->_hideSwitchToParentConfirm) {
                $switchToParentJsMethod = <<<JS
ListingProductVariationHandlerObj
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

    public function callbackColumnWalmartSku($value, $row, $column, $isExport)
    {
        $isVariationParent = $row->getData('is_variation_parent');

        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        $productId = $row->getData('id');

        if ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
            !$row->getData('is_variation_parent'))
        {
            $value = <<<HTML
<div class="walmart-sku">
    {$value}&nbsp;&nbsp;
    <a href="#" class="walmart-sku-edit"
       onclick="ListingGridHandlerObj.editChannelDataHandler.showEditSkuPopup({$productId})">(edit)</a>
</div>
HTML;
        }

        if (!$isVariationParent &&
            ($row->getData('is_details_data_changed') || $row->getData('is_online_price_invalid')))
        {
            $msg = '';

            if ($row->getData('is_details_data_changed')) {
                $message = <<<HTML
Item Details, e.g. Product Tax Code, Lag Time, Shipping, Description, Image, Category, etc. settings, need to be
updated on Walmart.<br>
To submit new Item Details, apply the Revise action. Use the Advanced Filter to select all Items with the Details
changes and update them in bulk.
HTML;
                $msg .= '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            }

            if ($row->getData('is_online_price_invalid')) {
                $message = <<<HTML
Item Price violates Walmart pricing rules. Please adjust the Item Price to comply with the Walmart requirements.<br>
Once the changes are applied, Walmart Item will become Active automatically.
HTML;
                $msg .= '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            }

            if (empty($msg)) {
                return $value;
            }

            $value .= <<<HTML
<div style="float:right; width: 16px">
    <img id="map_link_defected_message_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none; max-width: 400px;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</div>
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
        $channelUrl = $row->getData('channel_url');

        if (!empty($channelUrl)) {
            $gtinHtml = <<<HTML
<a href="{$channelUrl}" target="_blank">{$gtin}</a>
HTML;
        }

        $html = '<div class="walmart-identifiers-gtin">'.$gtinHtml;

        if ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
            !$row->getData('is_variation_parent'))
        {
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
<div style="float:right; width: 16px;">
    <img class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
        <div class="walmart-identifiers">
            {$htmlAdditional}
        </div>
    </span>
</div>
HTML;
        }

        return $html;
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if (!$row->getData('is_variation_parent')) {
            if ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
            }

            if ($value === null || $value === '' ||
                ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
                 !$row->getData('is_online_price_invalid')))
            {
                return Mage::helper('M2ePro')->__('N/A');
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($row->getData('variation_child_statuses'));

        if (empty($variationChildStatuses) || $value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $activeChildrenCount = 0;
        foreach ($variationChildStatuses as $childStatus => $count) {
            if ($childStatus == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                continue;
            }

            $activeChildrenCount += (int)$count;
        }

        if ($activeChildrenCount == 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ((!$row->getData('is_variation_parent') &&
            $row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlineMinPrice = (float)$row->getData('min_online_price');
        $onlineMaxPrice = (float)$row->getData('max_online_price');

        if (empty($onlineMinPrice) ||
            ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
             !$row->getData('is_online_price_invalid')))
        {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');
        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $marketplaceId)
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

        return $resultHtml;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId  = (int)$row->getData('id');
        $isVariationParent = (bool)(int)$row->getData('is_variation_parent');
        $additionalData    = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $html = $this->getViewLogIconHtml($listingProductId, $isVariationParent);

        if (!empty($additionalData['synch_template_list_rules_note'])) {
            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage(
                $additionalData['synch_template_list_rules_note']
            );

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

        $resetHtml = '';
        if ($row->getData('walmart_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
            !$row->getData('is_online_price_invalid'))
        {
            $resetHtml = <<<HTML
<br/>
<span style="color: gray">[Can be fixed]</span>
HTML;
        }

        if (!$isVariationParent) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing_Product', $listingProductId);

            $statusChangeReasons = $listingProduct->getChildObject()->getStatusChangeReasons();

            return $html
                . $this->getProductStatus($row, $row->getData('walmart_status'), $statusChangeReasons)
                . $resetHtml
                . $this->getScheduledTag($row)
                . $this->getLockedTag($row);
        } else {
            $statusNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
            $statusListed    = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            $statusStopped   = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            $statusBlocked   = Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;

            $variationChildStatuses = $row->getData('variation_child_statuses');
            if (empty($variationChildStatuses)) {
                return $html
                    . $this->getProductStatus($row, $statusNotListed)
                    . $this->getScheduledTag($row)
                    . $this->getLockedTag($row);
            }

            $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($variationChildStatuses);

            $sortedStatuses = array();

            if (isset($variationChildStatuses[$statusNotListed])) {
                $sortedStatuses[$statusNotListed] = $variationChildStatuses[$statusNotListed];
            }

            if (isset($variationChildStatuses[$statusListed])) {
                $sortedStatuses[$statusListed] = $variationChildStatuses[$statusListed];
            }

            if (isset($variationChildStatuses[$statusStopped])) {
                $sortedStatuses[$statusStopped] = $variationChildStatuses[$statusStopped];
            }

            if (isset($variationChildStatuses[$statusBlocked])) {
                $sortedStatuses[$statusBlocked] = $variationChildStatuses[$statusBlocked];
            }

            $linkTitle = Mage::helper('M2ePro')->__('Show all Child Products with such Status');

            foreach ($sortedStatuses as $status => $productsCount) {
                if (empty($productsCount)) {
                    continue;
                }

                $filter = base64_encode('status=' . $status);

                $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                $generalId = $row->getData('general_id');
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $productsCount = <<<HTML
<a onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}', '{$filter}')"
   class="hover-underline"
   title="{$linkTitle}"
   href="javascript:void(0)">[{$productsCount}]</a>
HTML;

                $html .= $this->getProductStatus($row, $status) . '&nbsp;'. $productsCount . '<br/>';
            }

            $html .= $this->getScheduledTag($row) . $this->getLockedTag($row);
        }

        return $html;
    }

    protected function getProductStatus($row, $status, $statusChangeReasons = array())
    {
        $html = '';
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html = '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html = '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html ='<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';
                break;
            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html ='<span style="color: orange; font-weight: bold;">' .
                    Mage::helper('M2ePro')->__('Inactive (Blocked)') . '</span>';
                break;
        }

        return $html .
               $this->getStatusChangeReasons($statusChangeReasons);
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

    protected function getLockedTag($row)
    {
        $html = '';

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        $childCount = 0;

        foreach ($tempLocks as $lock) {
            switch ($lock->getTag()) {
                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'child_products_in_action':
                    $childCount++;
                    break;

                default:
                    break;
            }
        }

        if ($childCount > 0) {
            $html .= '<br/><span style="color: #605fff">[Child(s) in Action...]</span>';
        }

        return $html;
    }

    protected function getScheduledTag($row)
    {
        $html = '';

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row['id']);

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
                    $reviseParts = implode(', ', $reviseParts);
                    $html .= '<br/><span style="color: #605fff">[Revise of '.$reviseParts.' is Scheduled...]</span>';
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
            $collection->addFieldToFilter($index, Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
                       ->addFieldToFilter('is_online_price_invalid', 0);
        }
    }

    // ---------------------------------------

    public function getViewLogIconHtml($listingProductId, $isVariationParent)
    {
        $listingProductId = (int)$listingProductId;

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Get last messages
        // ---------------------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator','listing_product_id')
            )
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
            ->order(array('id DESC'))
            ->limit(30);

        if ($isVariationParent) {
            $dbSelect->where('listing_product_id = ? OR parent_listing_product_id = ?', $listingProductId);
        } else {
            $dbSelect->where('listing_product_id = ?', $listingProductId);
        }

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
                        'action_id' => $lastActionId,
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
                'action_id' => $lastActionId,
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

        if ($isVariationParent) {
            foreach ($actionsRows as &$actionsRow) {
                if (!empty($actionsRow['items']) && $actionsRow['items'][0]['listing_product_id']==$listingProductId) {
                    continue;
                }

                $actionsRow['action_in_progress'] = $this->isActionInProgress($actionsRow['action_id']);

                $descArr = array();
                foreach ($actionsRow['items'] as $key => &$item) {
                    if (array_key_exists($item['description'], $descArr)) {
                        $descArr[$item['description']]['count']++;
                        unset($actionsRow['items'][$key]);
                        continue;
                    }

                    $item['count'] = 1;
                    $descArr[$item['description']] = $item;
                }

                $actionsRow['items'] = array_values($descArr);
            }
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
            'entity_id' => $listingProductId,
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
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
            Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT,
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
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING:
                $string = Mage::helper('M2ePro')->__('Remove from Listing');
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

    public function isActionInProgress($actionId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Get last messages
        // ---------------------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Processing')->getMainTable()
            )
            ->where('params REGEXP \'"logs_action_id":'.$actionId.'\'')
            ->limit(1);

        $result = $connRead->query($dbSelect)->fetch();
        return  $result !== false;
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

    protected function _getStore()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get store filter
        // ---------------------------------------
        $storeId = $listing['store_id'];
        // ---------------------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            ListingGridHandlerObj.afterInitPage();
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

    //########################################
}
