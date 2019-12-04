<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Amazon_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
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
            'additional_data', 'hide_switch_to_individual_confirm', 0
        );

        $this->_hideSwitchToParentConfirm = $listing->getSetting(
            'additional_data', 'hide_switch_to_parent_confirm', 0
        );

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingViewAmazonGrid'.$listingData['id']);
        // ---------------------------------------

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
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
            'M2ePro/adminhtml_amazon_listing_view_modeSwitcher'
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

        if ($this->isFilterOrSortByPriceIsUsed('online_price', 'amazon_online_price')) {
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
                'amazon_status'   => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$listingData['id']
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_regular_price'           => 'online_regular_price',
                'online_regular_sale_price'      => 'IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  NULL
                )',
                'online_regular_sale_price_start_date'   => 'online_regular_sale_price_start_date',
                'online_regular_sale_price_end_date'     => 'online_regular_sale_price_end_date',
                'online_business_price'          => 'online_business_price',
                'online_business_discounts'      => 'online_business_discounts',
                'is_repricing'                   => 'is_repricing',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
                'defected_messages'              => 'defected_messages',
                'is_details_data_changed'        => 'is_details_data_changed',
                'is_images_data_changed'         => 'is_images_data_changed',
                'variation_parent_afn_state'       => 'variation_parent_afn_state',
                'variation_parent_repricing_state' => 'variation_parent_repricing_state',
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->getSelect()->joinLeft(
            array('malpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            '(`alp`.`listing_product_id` = `malpr`.`listing_product_id`)',
            array(
                'is_repricing_disabled' => 'is_online_disabled',
                'is_repricing_inactive' => 'is_online_inactive',
            )
        );

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'         => Mage::helper('M2ePro')->__('Product ID'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'number',
                'index'          => 'entity_id',
                'filter_index'   => 'entity_id',
                'frame_callback' => array($this, 'callbackColumnListingProductId')
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
                'width'          => '150px',
                'type'           => 'text',
                'index'          => 'amazon_sku',
                'filter_index'   => 'amazon_sku',
                'frame_callback' => array($this, 'callbackColumnAmazonSku')
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '140px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'frame_callback' => array($this, 'callbackColumnGeneralId')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '70px',
                'type'                      => 'number',
                'index'                     => 'online_qty',
                'filter_index'              => 'online_qty',
                'frame_callback'            => array($this, 'callbackColumnAvailableQty'),
                'filter'                    => 'M2ePro/adminhtml_amazon_grid_column_filter_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

        if ($dir == 'desc') {
            $priceSortField = 'max_online_price';
        } else {
            $priceSortField = 'min_online_price';
        }

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '110px',
            'type' => 'number',
            'index' => $priceSortField,
            'filter_index' => $priceSortField,
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        );

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled()) {
            $priceColumn['filter'] = 'M2ePro/adminhtml_amazon_grid_column_filter_price';
        }

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn(
            'status', array(
                'header' => Mage::helper('M2ePro')->__('Status'),
                'width' => '155px',
                'index' => 'amazon_status',
                'filter_index' => 'amazon_status',
                'type' => 'options',
                'sortable' => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus'),
                'filter_condition_callback' => array($this, 'callbackFilterStatus')
            )
        );

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
            'asin_isbn'          => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'description_policy' => Mage::helper('M2ePro')->__('Description Policy'),
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
            'label'    => Mage::helper('M2ePro')->__('Remove from Channel & Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'assignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Search Automatically'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'asin_isbn'
        );

        $this->getMassactionBlock()->addItem(
            'newGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign Settings for New ASIN/ISBN'),
            'url'      => '',
            ), 'asin_isbn'
        );

        $this->getMassactionBlock()->addItem(
            'unassignGeneralId', array(
            'label'    => Mage::helper('M2ePro')->__('Reset Information'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'asin_isbn'
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))
                                                               ->getSku();
        }

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $generalId = $row->getData('general_id');

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

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

            if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner() &&
                !empty($productAttributes) && $variationManager->getTypeModel()->isActualProductAttributes()
            ) {
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
    <img width="12" height="12" style="height: 12px; width: 12px; vertical-align: middle;" src="{$iconSettingsPath}">
    </a>
HTML;
            }

            $value .= '</div>';

            if (!empty($generalId) || $amazonListingProduct->isGeneralIdOwner()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
                $parentType = $variationManager->getTypeModel();

                $linkContent = Mage::helper('M2ePro')->__('Manage Variations');
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $problemStyle = '';
                $problemIcon = '';

                $linkTitle = Mage::helper('M2ePro')->__('Open Manage Variations Tool');

                if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                    if (!$parentType->hasChannelTheme() || !$parentType->hasMatchedAttributes()) {
                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold; color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                } elseif (!empty($generalId)) {
                    if (!$parentType->hasMatchedAttributes()) {
                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    } elseif (($listingProduct->getChildObject()->isGeneralIdOwner() &&
                              !$parentType->hasChannelTheme()) ||
                              $this->hasChildWithWarning($listingProductId)) {
                        $linkTitle = Mage::helper('M2ePro')->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/warning.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                }

                $value .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
    <a {$problemStyle}href="javascript:"
    onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}')"
    title="{$linkTitle}">{$linkContent}</a>&nbsp;{$problemIcon}
</div>
HTML;
            }

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

        if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
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

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if ((!$row->getData('is_variation_parent') &&
            $row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_variation_parent') &&
            ($row->getData('defected_messages') ||
             $row->getData('is_details_data_changed') ||
             $row->getData('is_images_data_changed'))
        ) {
            $defectedMessages = Mage::helper('M2ePro')->jsonDecode($row->getData('defected_messages'));
            if (empty($defectedMessages)) {
                $defectedMessages = array();
            }

            $msg = '';

            if ($row->getData('is_details_data_changed')) {
                $message = <<<HTML
Item Details, e.g. Condition Note, Gift Message, Gift Wrap, Item Description, Shipping or Product Tax Code settings,
need to be updated on Amazon.<br>
To submit new Item Details, apply the Revise action. Use the Advanced Filter to select all Items with the Details
changes and update them in bulk.
HTML;
                $msg .= '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            }

            if ($row->getData('is_images_data_changed')) {
                $message = <<<HTML
Item Images need to be updated on Amazon. To submit new Item Images, apply the Revise action.<br>
Use the Advanced Filter to select all Items with the Images changes and update them in bulk.
HTML;
                $msg .= '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            }

            foreach ($defectedMessages as $message) {
                if (empty($message['message'])) {
                    continue;
                }

                $msg .= '<p>'.$message['message'] . '&nbsp;';
                if (!empty($message['value'])) {
                    $msg .= Mage::helper('M2ePro')->__('Current Value') . ': "' . $message['value'] . '"';
                }

                $msg .= '</p>';
            }

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

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            if ($row->getData('is_general_id_owner') == 1) {
                return $this->getGeneralIdColumnValueGeneralIdOwner($row);
            }

            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $listingProductId = $row->getData('id');

        if (!$row->getData('is_variation_parent')) {
            if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
            }

            if ((bool)$row->getData('is_afn_channel')) {
                $sku = $row->getData('amazon_sku');

                if (empty($sku)) {
                    return Mage::helper('M2ePro')->__('AFN');
                }

                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
                    'Listing_Product', $listingProductId
                );

                $afn = Mage::helper('M2ePro')->__('AFN');
                $total = Mage::helper('M2ePro')->__('Total');
                $inStock = Mage::helper('M2ePro')->__('In Stock');
                $accountId = $listingProduct->getListing()->getAccountId();

                return <<<HTML
<div id="m2ePro_afn_qty_value_{$listingProductId}">
    <span class="m2ePro-online-sku-value" productId="{$listingProductId}" style="display: none">{$sku}</span>
    <span class="m2epro-empty-afn-qty-data" style="display: none">{$afn}</span>
    <div class="m2epro-afn-qty-data" style="display: none">
        <div class="total">{$total}: <span></span></div>
        <div class="in-stock">{$inStock}: <span></span></div>
    </div>
    <a href="javascript:void(0)"
        onclick="AmazonListingAfnQtyHandlerObj.showAfnQty(this,'{$sku}',{$listingProductId}, {$accountId})">
        {$afn}</a>
</div>
HTML;
            }

            if ($value === null || $value === '') {
                return '<i style="color:gray;">receiving...</i>';
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        if ($row->getData('general_id') == '') {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($row->getData('variation_child_statuses'));

        if (empty($variationChildStatuses)) {
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

        if (!(bool)$row->getData('is_afn_channel')) {
            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        $resultValue = Mage::helper('M2ePro')->__('AFN');
        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $filter = base64_encode('online_qty[afn]=1');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
        $vpmt = addslashes($vpmt);

        $linkTitle = Mage::helper('M2ePro')->__('Show AFN Child Products.');
        $afnCountWord = !empty($additionalData['afn_count']) ? $additionalData['afn_count']
                                                             : Mage::helper('M2ePro')->__('show');

        $resultValue = $resultValue."&nbsp;<a href=\"javascript:void(0)\"
                           class=\"hover-underline\"
                           title=\"{$linkTitle}\"
                           onclick=\"ListingGridHandlerObj.variationProductManageHandler.openPopUp(
                            {$listingProductId}, '{$vpmt}', '{$filter}'
                        )\">[".$afnCountWord."]</a>";

        return <<<HTML
<div>{$value}</div>
<div>{$resultValue}</div>
HTML;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((!$row->getData('is_variation_parent') &&
            $row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $listingProductId = (int)$row->getData('id');

        $repricingHtml ='';

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() && $row->getData('is_repricing')) {
            if ($row->getData('is_variation_parent')) {
                $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

                $repricingManagedCount = isset($additionalData['repricing_managed_count'])
                    ? $additionalData['repricing_managed_count'] : null;

                $repricingNotManagedCount = isset($additionalData['repricing_not_managed_count'])
                    ? $additionalData['repricing_not_managed_count'] : null;

                if ($repricingManagedCount && $repricingNotManagedCount) {
                    $image = 'money_mixed';
                    $countHtml = '['.$repricingManagedCount.'/'.$repricingNotManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'Some Child Products of this Parent ASIN are disabled or unable to be repriced
                        on Amazon Repricing Tool.<br>
                        <strong>Note:</strong> the Price values shown in the grid may differ from Amazon ones.
                        It is caused by some delay in data synchronization between M2E Pro and Repricing Tool.'
                    );
                } elseif ($repricingManagedCount) {
                    $image = 'money';
                    $countHtml = '['.$repricingManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'All Child Products of this Parent are Enabled for dynamic repricing. <br>
                        <strong>Please note</strong> that the Price value(s) shown in the grid might be different
                        from the actual one from Amazon. It is caused by the delay in the values updating
                        made via the Repricing Service.'
                    );
                } elseif ($repricingNotManagedCount) {
                    $image = 'money_disabled';
                    $countHtml = '['.$repricingNotManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'The Child Products of this Parent ASIN are disabled or
                                                       unable to be repriced on Amazon Repricing Tool.'
                    );
                } else {
                    $image = 'money';
                    $countHtml = Mage::helper('M2ePro')->__('[show]');
                    $text = Mage::helper('M2ePro')->__(
                        'Some Child Products of this Parent are managed by the Repricing Service. <br>
                        <strong>Please note</strong> that the Price value(s) shown in the grid might be
                        different from the actual one from Amazon. It is caused by the delay in the
                        values updating made via the Repricing Service.'
                    );
                }

                $filter = base64_encode('online_price[is_repricing]=1');

                $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
                $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                $generalId = $row->getData('general_id');
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $linkTitle = Mage::helper('M2ePro')->__('Show Child Products managed by Amazon Repricing Service.');

                $repricingHtml = <<<HTML
<br /><span style="float:right; text-align: left;">
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/'.$image.'.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>&nbsp;<a href="javascript:void(0)"
       class="hover-underline"
       title="{$linkTitle}"
       onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp(
        {$listingProductId}, '{$vpmt}', '{$filter}'
    )">$countHtml</a>
</span>
HTML;
            } elseif (!$row->getData('is_variation_parent')) {
                $image = 'money';
                $text = Mage::helper('M2ePro')->__(
                    'This Product is used by Amazon Repricing Tool, so its Price cannot be managed via M2E Pro.<br>
                    <strong>Please note</strong> that the Price value shown in the grid might be different
                    from the actual one from Amazon. It is caused by the delay in the values
                    updating made via the Repricing Service.'
                );

                if ((int)$row->getData('is_repricing_disabled') == 1 ||
                    (int)$row->getData('is_repricing_inactive') == 1) {
                    $image = 'money_disabled';
                    $text = Mage::helper('M2ePro')->__(
                        'This Item is disabled or unable to be repriced on Amazon Repricing Tool.
                        Its Price is updated via M2E Pro.'
                    );
                }

                $repricingHtml = <<<HTML
<span style="float:right; text-align: left;">&nbsp;
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/'.$image.'.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>
</span>
HTML;
            }
        }

        $onlineMinRegularPrice = (float)$row->getData('min_online_regular_price');
        $onlineMaxRegularPrice = (float)$row->getData('max_online_regular_price');

        $onlineMinBusinessPrice = (float)$row->getData('min_online_business_price');
        $onlineMaxBusinessPrice = (float)$row->getData('max_online_business_price');

        if (empty($onlineMinRegularPrice) && empty($onlineMinBusinessPrice)) {
            if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent')
            ) {
                return Mage::helper('M2ePro')->__('N/A') . $repricingHtml;
            } else {
                return '<i style="color:gray;">receiving...</i>' . $repricingHtml;
            }
        }

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');
        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', $marketplaceId)
            ->getChildObject()
            ->getDefaultCurrency();

        if ($row->getData('is_variation_parent')) {
            $onlineRegularPriceStr = '<span style="color: #f00;">0</span>';
            if (!empty($onlineMinRegularPrice) && !empty($onlineMaxRegularPrice)) {
                $onlineMinRegularPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMinRegularPrice);
                $onlineMaxRegularPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMaxRegularPrice);

                $onlineRegularPriceStr = $onlineMinRegularPriceStr
                                         .(($onlineMinRegularPrice != $onlineMaxRegularPrice)?' - '
                                         .$onlineMaxRegularPriceStr:'');
            }

            $onlineBusinessPriceStr = '';
            if (!empty($onlineMinBusinessPrice) && !empty($onlineMaxBusinessPrice)) {
                $onlineMinBusinessPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMinBusinessPrice);
                $onlineMaxBusinessPriceStr = Mage::app()->getLocale()
                    ->currency($currency)
                    ->toCurrency($onlineMaxBusinessPrice);

                $onlineBusinessPriceStr = '<br /><strong>B2B: </strong>'
                                          .$onlineMinBusinessPriceStr
                                          .(($onlineMinBusinessPrice != $onlineMaxBusinessPrice)?' - '
                                          .$onlineMaxBusinessPriceStr:'');
            }

            return $onlineRegularPriceStr.$onlineBusinessPriceStr.$repricingHtml;
        }

        $onlineRegularPrice = $row->getData('online_regular_price');
        if ((float)$onlineRegularPrice <= 0) {
            $regularPriceValue = '<span style="color: #f00;">0</span>';
        } else {
            $regularPriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineRegularPrice);
        }

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            !$row->getData('is_repricing_inactive') &&
            !$row->getData('is_variation_parent')
        ) {
            $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
            $accountId = $listingData['account_id'];
            $sku = $row->getData('amazon_sku');

            $regularPriceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceHandlerObj.showRepricingPrice()">
    {$regularPriceValue}</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_regular_sale_price');
        if (!$row->getData('is_variation_parent') && (float)$salePrice > 0) {
            $currentTimestamp = strtotime(
                Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d 00:00:00')
            );

            $startDateTimestamp = strtotime($row->getData('online_regular_sale_price_start_date'));
            $endDateTimestamp   = strtotime($row->getData('online_regular_sale_price_end_date'));

            if ($currentTimestamp <= $endDateTimestamp) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

                $fromDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_start_date'), $dateFormat
                )->toString($dateFormat);
                $toDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_end_date'), $dateFormat
                )->toString($dateFormat);

                $intervalHtml = '<span><img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message" style="display:none;
                                                                  text-align: left;
                                                                  width: 120px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    <strong>From:</strong> '.$fromDate.'<br/>
                                    <strong>To:</strong> '.$toDate.'
                                </span>
                            </span></span>';

                $salePriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($salePrice);

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < (float)$onlineRegularPrice
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'
                                   .$regularPriceValue
                                   .'</span>' .
                                    $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.$salePriceValue;
                } else {
                    $resultHtml .= $regularPriceValue . $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.
                        '<span style="color:gray;">'.'&nbsp;'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $regularPriceValue . $repricingHtml;
        }

        $onlineBusinessPrice = $row->getData('online_business_price');
        if ((float)$onlineBusinessPrice > 0) {
            $businessPriceValue = '<strong>B2B:</strong> '
                                  .Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBusinessPrice);

            $businessDiscounts = $row->getData('online_business_discounts');
            if (!empty($businessDiscounts) && $businessDiscounts = json_decode($businessDiscounts, true)) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $discountsHtml = '';

                foreach ($businessDiscounts as $qty => $price) {
                    $price = Mage::app()->getLocale()->currency($currency)->toCurrency($price);
                    $discountsHtml .= 'QTY >= '.(int)$qty.', price '.$price.'<br />';
                }

                $discountsHtml = ' <span><img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message" style="display:none;
                                                                  text-align: left;
                                                                  width: 150px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    '.$discountsHtml.'
                                </span>
                            </span></span>';

                $businessPriceValue .= $discountsHtml;
            }

            if (!empty($resultHtml)) {
                $businessPriceValue = '<br />'.$businessPriceValue;
            }

            $resultHtml .= $businessPriceValue;
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

        if (!$isVariationParent) {
            return $html . $this->getProductStatus($row->getData('amazon_status'))
                .$this->getScheduledTag($row).$this->getLockedTag($row);
        } else {
            $statusUnknown   = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
            $statusNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
            $statusListed    = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            $statusStopped   = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            $statusBlocked   = Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;

            $generalId = $row->getData('general_id');
            $variationChildStatuses = $row->getData('variation_child_statuses');
            if (empty($generalId) || empty($variationChildStatuses)) {
                return $html . $this->getProductStatus($statusNotListed) . $this->getScheduledTag($row).
                    $this->getLockedTag($row);
            }

            $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($variationChildStatuses);

            $sortedStatuses = array();
            if (isset($variationChildStatuses[$statusUnknown])) {
                $sortedStatuses[$statusUnknown] = $variationChildStatuses[$statusUnknown];
            }

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

                $html .= $this->getProductStatus($status) . '&nbsp;'. $productsCount . '<br/>';
            }

            $html .= $this->getScheduledTag($row) . $this->getLockedTag($row);
        }

        return $html;
    }

    protected function getProductStatus($status)
    {
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Unknown') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                return'<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                return'<span style="color: orange; font-weight: bold;">' .
                    Mage::helper('M2ePro')->__('Inactive (Blocked)') . '</span>';
        }

        return '';
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

                case 'switch_to_afn_action':
                    $html .= '<br/><span style="color: #605fff">[Switch to AFN in Progress...]</span>';
                    break;

                case 'switch_to_mfn_action':
                    $html .= '<br/><span style="color: #605fff">[Switch to MFN in Progress...]</span>';
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
                    $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isRegularPriceAllowed() || $configurator->isBusinessPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isDetailsAllowed()) {
                            $reviseParts[] = 'Details';
                        }

                        if ($configurator->isImagesAllowed()) {
                            $reviseParts[] = 'Images';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $html .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                             .' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
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

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ((int)$value['afn'] == 1) {
                $where .= 'is_afn_channel = 1';
            } else {
                $partialFilter = Ess_M2ePro_Model_Amazon_Listing_Product::VARIATION_PARENT_IS_AFN_STATE_PARTIAL;
                $where .= "(is_afn_channel = 0 OR variation_parent_afn_state = {$partialFilter})";
            }
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

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() &&
            (isset($value['is_repricing']) && $value['is_repricing'] !== '')
        ) {
            if (!empty($condition)) {
                $condition = '(' . $condition . ') AND ';
            }

            if ((int)$value['is_repricing'] == 1) {
                $condition .= 'is_repricing = 1';
            } else {
                $partialFilter = Ess_M2ePro_Model_Amazon_Listing_Product::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL;
                $condition .= "(is_repricing = 0 OR variation_parent_repricing_state = {$partialFilter})";
            }
        }

        $collection->getSelect()->having($condition);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            "lp.status = {$value} OR
            (alp.variation_child_statuses REGEXP '\"{$value}\":[^0]') AND alp.is_variation_parent = 1"
        );
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
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT
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
                $string = Mage::helper('M2ePro')->__('Remove from Channel');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Remove from Channel & Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING:
                $string = Mage::helper('M2ePro')->__('Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Switch to AFN');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Switch to MFN');
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
        return $this->getUrl('*/adminhtml_amazon_listing/viewGrid', array('_current'=>true));
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

    protected function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('amazon_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
        }

        // ---------------------------------------

        // ---------------------------------------
        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        // ---------------------------------------

        // ---------------------------------------
        $lpId = $row->getData('id');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }

        $productTitle = Mage::helper('M2ePro')->__('Assign ASIN/ISBN For &quot;%product_title%&quot;', $productTitle);
        $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
        // ---------------------------------------

        // ---------------------------------------

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {
            $tip = Mage::helper('M2ePro')->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $iconPath.'processing.gif';

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}"><img src="{$iconSrc}" alt=""></a>
HTML;
        }

        // ---------------------------------------

        // ---------------------------------------
        $searchSettingsData = $row->getData('search_settings_data');

        $suggestData = array();
        if ($searchSettingsData !== null) {
            $searchSettingsData = Mage::helper('M2ePro')->jsonDecode($searchSettingsData);
            !empty($searchSettingsData['data']) && $suggestData = $searchSettingsData['data'];
        }

        // ---------------------------------------

        $na = Mage::helper('M2ePro')->__('N/A');

        if (!empty($suggestData)) {
            $tip = Mage::helper('M2ePro')->__('Choose ASIN/ISBN from the list');
            $iconSrc = $iconPath.'list.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
        }

        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND) {
            $tip = Mage::helper('M2ePro')->__(
                'There were no Products found on Amazon according to the Listing Search Settings.'
            );
            $tip = Mage::helper('M2ePro')->escapeJs($tip);

            $iconSrc = $iconPath.'error.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript: void(0);" title="{$tip}"
    onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId},'{$tip}');">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
        }

        $tip = Mage::helper('M2ePro')->__('Search for ASIN/ISBN');
        $iconSrc = $iconPath.'search.png';

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
    }

    protected function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');
        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $marketplaceId
        );

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        if ((int)$row->getData('amazon_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = Mage::helper('M2ePro')->jsonDecode($generalIdSearchInfo);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {
            $tip = Mage::helper('M2ePro')->__('ASIN/ISBN was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;
        } else {
            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text . $generalIdOwnerHtml;
        }

        $listingProductId = (int)$row->getData('id');

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $variationChildStatuses = $row->getData('variation_child_statuses');

        if ($variationManager->isVariationParent() && !empty($variationChildStatuses)) {
            $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($variationChildStatuses);
            unset($variationChildStatuses[Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);

            foreach ($variationChildStatuses as $variationChildStatus) {
                if (!empty($variationChildStatus)) {
                    return $text . $generalIdOwnerHtml;
                }
            }
        }

        $tip = Mage::helper('M2ePro')->__('Unassign ASIN/ISBN');
        $iconSrc = $iconPath.'unassign.png';

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
    title="{$tip}">
    <img src="{$iconSrc}" width="16" height="16"/></a>{$generalIdOwnerHtml}
HTML;

        return $text;
    }

    protected function getGeneralIdColumnValueGeneralIdOwner($row)
    {
        $text = Mage::helper('M2ePro')->__('New ASIN/ISBN');

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text;
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');

        $tip = Mage::helper('M2ePro')->__('Unassign ASIN/ISBN');
        $iconSrc = $iconPath.'unassign.png';

        $lpId = $row->getData('id');

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$lpId});"
    title="{$tip}"><img src="{$iconSrc}" width="16" height="16"/></a>
HTML;
        return $text;
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
            $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_amazon_listing_product');

            $select = $connRead->select();
            $select->distinct(true);
            $select->from(array('alp' => $tableAmazonListingProduct), array('variation_parent_id'))
                ->where('variation_parent_id IN (?)', $productsIds)
                ->where(
                    'is_variation_product_matched = 0 OR
                    (general_id IS NOT NULL AND is_variation_channel_matched = 0)'
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
