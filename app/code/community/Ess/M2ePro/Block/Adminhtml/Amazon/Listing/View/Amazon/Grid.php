<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Amazon_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    const ACTUAL_QTY_EXPRESSION = 'IF(alp.is_afn_channel = 1, alp.online_afn_qty, alp.online_qty)';

    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
    protected $_sellingFormatTemplate = null;

    protected $_lockedDataCache = array();

    protected $_childProductsWarningsData;
    protected $_parentAndChildReviseScheduledCache = array();

    protected $_hideSwitchToIndividualConfirm;
    protected $_hideSwitchToParentConfirm;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->_hideSwitchToIndividualConfirm = $this->_listing->getSetting(
            'additional_data', 'hide_switch_to_individual_confirm', 0
        );

        $this->_hideSwitchToParentConfirm = $this->_listing->getSetting(
            'additional_data', 'hide_switch_to_parent_confirm', 0
        );

        $this->setId('amazonListingViewGrid' . $this->_listing->getId());

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
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
            'M2ePro/adminhtml_amazon_listing_view_modeSwitcher'
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
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'status'          => 'status',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$this->_listing->getId()
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
                'online_afn_qty'                 => 'online_afn_qty',
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

        $collection->addExpressionAttributeToSelect(
            'online_actual_qty',
            self::ACTUAL_QTY_EXPRESSION,
            array()
        );

        if ($this->isFilterOrSortByPriceIsUsed('online_price', 'amazon_online_price')) {
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
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
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
            'amazon_sku', array(
                'header'         => Mage::helper('M2ePro')->__('SKU'),
                'align'          => 'left',
                'width'          => '150px',
                'type'           => 'text',
                'index'          => 'amazon_sku',
                'filter_index'   => 'amazon_sku',
                'renderer'       => 'M2ePro/adminhtml_amazon_grid_column_renderer_sku'
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '150px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'filter'         => 'M2ePro/adminhtml_amazon_grid_column_filter_generalId',
                'frame_callback' => array($this, 'callbackColumnGeneralId'),
                'filter_condition_callback' => array($this, 'callbackFilterGeneralId')
            )
        );

        $this->addColumn(
            'qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '70px',
                'type'                      => 'number',
                'index'                     => 'online_actual_qty',
                'filter_index'              => 'online_actual_qty',
                'renderer'                  => 'M2ePro/adminhtml_amazon_grid_column_renderer_qty',
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
            'filter_condition_callback' => array($this, 'callbackFilterPrice'),
            'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_price',
        );

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn(
            'status', array(
                'header'        => Mage::helper('M2ePro')->__('Status'),
                'width'         => '140px',
                'index'         => 'status',
                'filter_index'  => 'status',
                'type'          => 'options',
                'sortable'      => false,
                'options'       => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Incomplete')
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
        $helper = Mage::helper('M2ePro');

        $productTitle = $helper->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>' . $helper->__('SKU') . ':</strong> ' . $helper->escapeHtml($tempSku) . '<br/>';

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
    <img width="12" height="12" style="height: 12px; width: 12px; vertical-align: middle;" src="{$iconSettingsPath}">
    </a>
HTML;
            }

            $value .= '</div>';

            if (!empty($generalId) || $amazonListingProduct->isGeneralIdOwner()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
                $parentType = $variationManager->getTypeModel();

                $linkContent = $helper->__('Manage Variations');
                $vpmt = $helper->escapeJs(
                    $helper->__('Manage Variations of "%s" ', $productTitle)
                );
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $problemStyle = '';
                $problemIcon = '';

                $linkTitle = $helper->__('Open Manage Variations Tool');

                if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                    if (!$parentType->hasChannelTheme() || !$parentType->hasMatchedAttributes()) {
                        $linkTitle = $helper->__('Action Required');
                        $problemStyle = 'style="font-weight: bold; color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                } elseif (!empty($generalId)) {
                    if (!$parentType->hasMatchedAttributes()) {
                        $linkTitle = $helper->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    } elseif (($listingProduct->getChildObject()->isGeneralIdOwner() &&
                              !$parentType->hasChannelTheme()) ||
                              $this->hasChildWithWarning($listingProductId)) {
                        $linkTitle = $helper->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;" ';
                        $iconPath = $this->getSkinUrl('M2ePro/images/warning.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
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

        if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
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

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((!$row->getData('is_variation_parent') &&
                $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $listingProductId = (int)$row->getData('id');

        $repricingHtml ='';

        if ($row->getData('is_repricing')) {
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
       onclick="ListingGridObj.variationProductManageHandler.openPopUp(
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
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent')
            ) {
                return Mage::helper('M2ePro')->__('N/A') . $repricingHtml;
            } else {
                return '<i style="color:gray;">receiving...</i>' . $repricingHtml;
            }
        }

        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', $this->_listing->getMarketplaceId())
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
            $accountId = $this->_listing->getAccountId();
            $sku = $row->getData('amazon_sku');

            $regularPriceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceObj.showRepricingPrice()">
    {$regularPriceValue}</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_regular_sale_price');
        if (!$row->getData('is_variation_parent') && (float)$salePrice > 0) {
            /** @var Ess_M2ePro_Helper_Data $helper */
            $helper = Mage::helper('M2ePro');
            $currentTimestamp = (int)$helper->createGmtDateTime(
                $helper->getCurrentGmtDate(false, 'Y-m-d 00:00:00')
            )->format('U');

            $startDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_start_date'))
                ->format('U');
            $endDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_end_date'))
                ->format('U');

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
        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Renderer_Status $viewLogIcon */
        $status = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_grid_column_renderer_status');
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
                array('attribute' => 'sku', 'like' => '%'.$value.'%'),
                array('attribute' => 'amazon_sku', 'like' => '%'.$value.'%'),
                array('attribute' => 'name', 'like' => '%'.$value.'%')
            )
        );
    }

    protected function callbackFilterGeneralId($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue('input');
        if ($inputValue !== null) {
            $collection->addFieldToFilter('general_id', array('like' => '%' . $inputValue . '%'));
        }

        $selectValue = $column->getFilter()->getValue('select');
        if ($selectValue !== null) {
            $collection->addFieldToFilter('is_general_id_owner', $selectValue);
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
            $where .= self::ACTUAL_QTY_EXPRESSION . ' >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= self::ACTUAL_QTY_EXPRESSION . ' <= ' . (int)$value['to'];
        }

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where .= ' AND ';
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

        if (isset($value['is_repricing']) && $value['is_repricing'] !== '') {
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

    protected function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
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
   onclick="ListingGridObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
        }

        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_IDENTIFIER_INVALID) {
            $tip = Mage::helper('M2ePro')->__('Product ID not found.');
            $tip = Mage::helper('M2ePro')->escapeJs($tip);

            $iconSrc = $iconPath.'error.png';

            return <<<HTML
{$na} &nbsp;
<a href="javascript: void(0);" title="{$tip}"
    onclick="ListingGridObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId},'{$tip}');">
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
    onclick="ListingGridObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId},'{$tip}');">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
        }

        $tip = Mage::helper('M2ePro')->__('Search for ASIN/ISBN');
        $iconSrc = $iconPath.'search.png';

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}"
   onclick="ListingGridObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
    <img src="{$iconSrc}" alt="" width="16" height="16"></a>
HTML;
    }

    protected function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $generalId,
            $this->_listing->getMarketplaceId()
        );

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        $notASINAndNotISBN =  !Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId)
            && !Mage::helper('M2ePro')->isISBN($generalId);
        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            if ($notASINAndNotISBN) {
                return <<<HTML
<div style="display: flex; flex-direction: row; align-items: center;">
   <div>
    <span>{$generalId}</span>
    {$generalIdOwnerHtml}
</div>
    <div style="position:relative; left: 15px; top: -2px;">
    {$this->getTooltipHtml($this->__('Amazon returned UPC/EAN as the product ID'))}
    </div>
</div>
HTML;
            } else {
                return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
            }
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

        $tooltipHtml = '';
        if ($notASINAndNotISBN) {
            $text = $generalId;
            $tooltipHtml = $this->getTooltipHtml($this->__('Amazon returned UPC/EAN as the product ID'));
        }

        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];

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

        $result = <<<HTML
<div style="display: flex; flex-direction: row; align-items: center;">
    <span>{$text}</span>
    &nbsp;
    <a href="javascript:;"
        class="amazon-listing-view-icon amazon-listing-view-generalId-remove"
        onclick="ListingGridObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
        title="{$tip}"><img src="{$iconSrc}" width="16" height="16"/></a>
    <div style="position:relative; left: 15px; top: -2px;">
    {$tooltipHtml}
    </div>
</div>
{$generalIdOwnerHtml}
HTML;

        return $result;
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
    onclick="ListingGridObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$lpId});"
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

    protected function getTooltipHtml($content)
    {
        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        return <<<HTML
<span>
    <img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconSrc}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$helpIconSrc}" />
        <span style="color:gray;">
           {$content}
        </span>
    </span>
</span>
HTML;
    }
}
