<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Sellercentral_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Amazon_Template_SellingFormat */
    protected $_sellingFormatTemplate;

    protected $_lockedDataCache = array();

    protected $_parentAsins;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingViewSellercentralGrid'.$listingData['id']);
        // ---------------------------------------

        $this->_showAdvancedFilterProductsOption = false;

        $this->_sellingFormatTemplate = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Template_SellingFormat', $listingData['template_selling_format_id'], NULL,
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
                   ->joinStockItem(array('qty' => 'qty', 'is_in_stock' => 'is_in_stock'));

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
                'listing_id' => (int)$listingData['id'],
                'status' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN,
                )
            )
        );
        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'search_settings_status'         => 'search_settings_status',
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
                'variation_child_statuses'      => 'variation_child_statuses',
                'variation_parent_id'           => 'variation_parent_id',
                'defected_messages'              => 'defected_messages',
                'is_details_data_changed'        => 'is_details_data_changed',
                'is_images_data_changed'         => 'is_images_data_changed',
                'variation_parent_afn_state'       => 'variation_parent_afn_state',
                'variation_parent_repricing_state' => 'variation_parent_repricing_state',
            ),
            '{{table}}.is_variation_parent = 0'
        );

        $collection->getSelect()->columns(
            array(
            'min_online_price' => new Zend_Db_Expr(
                '
                IF (
                    `alp`.`online_regular_price` IS NULL,
                    `alp`.`online_business_price`,
                    IF (
                        `alp`.`online_regular_sale_price` IS NOT NULL AND
                        `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                        `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                        `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                        `alp`.`online_regular_sale_price`,
                        `alp`.`online_regular_price`
                    )
                )
            '
            )
            )
        );

        $collection->getSelect()->joinLeft(
            array('malpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
            '(`alp`.`listing_product_id` = `malpr`.`listing_product_id`)',
            array(
                'is_repricing_disabled' => 'is_online_disabled',
                'is_repricing_inactive' => 'is_online_inactive',
            )
        );
        // ---------------------------------------

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        // Set collection to grid
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

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '110px',
            'type' => 'number',
            'index' => 'min_online_price',
            'filter_index' => 'min_online_price',
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
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')->__('Inactive (Blocked)')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            $this->addColumn(
                'developer_action', array(
                    'header'     => Mage::helper('M2ePro')->__('Actions'),
                    'align'      => 'left',
                    'width'      => '100px',
                    'type'       => 'text',
                    'renderer'   => 'M2ePro/adminhtml_listing_view_grid_column_renderer_developerAction',
                    'index'      => 'value',
                    'filter'     => false,
                    'sortable'   => false,
                    'js_handler' => 'ListingGridHandlerObj'
                )
            );
        }

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
            'edit_fulfillment'   => Mage::helper('M2ePro')->__('Fulfillment')
        );

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled()) {
            $groups['edit_repricing'] = Mage::helper('M2ePro')->__('Repricing Tool');
        }

        $this->getMassactionBlock()->setGroups($groups);

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
            'switchToAfn', array(
            'label'    => Mage::helper('M2ePro')->__('Switch to AFN'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'edit_fulfillment'
        );

        $this->getMassactionBlock()->addItem(
            'switchToMfn', array(
            'label'    => Mage::helper('M2ePro')->__('Switch to MFN'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'edit_fulfillment'
        );

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getObject('Account', $listingData['account_id']);

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() &&
            $account->getChildObject()->isRepricing()) {
            $this->getMassactionBlock()->addItem(
                'showDetails', array(
                'label' => Mage::helper('M2ePro')->__('Show Details'),
                'url' => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ), 'edit_repricing'
            );

            $this->getMassactionBlock()->addItem(
                'addToRepricing', array(
                'label' => Mage::helper('M2ePro')->__('Add Item(s)'),
                'url' => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ), 'edit_repricing'
            );

            $this->getMassactionBlock()->addItem(
                'editRepricing', array(
                'label' => Mage::helper('M2ePro')->__('Edit Item(s)'),
                'url' => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ), 'edit_repricing'
            );

            $this->getMassactionBlock()->addItem(
                'removeFromRepricing', array(
                'label' => Mage::helper('M2ePro')->__('Remove Item(s)'),
                'url' => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ), 'edit_repricing'
            );
        }

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnListingProductId($value, $row, $column, $isExport)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = (int)$listingData['store_id'];

        $withoutImageHtml = '<a href="'
            .$this->getUrl(
                'adminhtml/catalog_product/edit',
                array('id' => $value)
            )
            .'" target="_blank">'.$value.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue(
                '/view/',
                'show_products_thumbnails'
            );
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($value);
        $magentoProduct->setStoreId($storeId);

        $imageResized = $magentoProduct->getThumbnailImage();
        if ($imageResized === null) {
            return $withoutImageHtml;
        }

        $imageHtml = $value.'<hr style="border: 1px solid silver; border-bottom: none;"><img src="'.
            $imageResized->getUrl().'" style="max-width: 100px; max-height: 100px;" />';
        $withImageHtml = str_replace('>'.$value.'<', '>'.$imageHtml.'<', $withoutImageHtml);

        return $withImageHtml;
    }

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

        $listingProduct = Mage::getModel('M2ePro/Listing_Product')->load($row->getData('id'));
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return $value;
        }

        if ($variationManager->isRelationChildType()) {
            $typeModel = $variationManager->getTypeModel();

            $productOptions = $typeModel->getProductOptions();
            $channelOptions = $typeModel->getChannelOptions();

            $parentTypeModel = $variationManager->getTypeModel()->getParentTypeModel();

            $virtualProductAttributes = array_keys($parentTypeModel->getVirtualProductAttributes());
            $virtualChannelAttributes = array_keys($parentTypeModel->getVirtualChannelAttributes());

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $typeModel->getParentListingProduct()->getChildObject();

            $matchedAttributes = $parentAmazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getMatchedAttributes();

            if (!empty($matchedAttributes)) {
                $sortedOptions = array();

                foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
                    $sortedOptions[$amazonAttr] = $channelOptions[$amazonAttr];
                }

                $channelOptions = $sortedOptions;
            }

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Magento Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($productOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualProductAttributes)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                !$option && $option = '--';
                $value .= '<span style="' . $style . '"><b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</span><br/>';
            }

            $value .= '</div>';

            $value .= '<div style="font-weight:bold;font-size: 11px;color: grey;margin-left: 7px;margin-top: 5px;">'.
                Mage::helper('M2ePro')->__('Amazon Variation') . '</div>';
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 24px">';
            foreach ($channelOptions as $attribute => $option) {
                $style = '';
                if (in_array($attribute, $virtualChannelAttributes)) {
                    $style = 'border-bottom: 2px dotted grey';
                }

                !$option && $option = '--';
                $value .= '<span style="' . $style . '"><b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '</span><br/>';
            }

            $value .= '</div>';

            return $value;
        }

        $productOptions = array();
        if ($listingProduct->getChildObject()->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $productOptions = $listingProduct->getChildObject()->getVariationManager()
                ->getTypeModel()->getProductOptions();
        }

        $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
        foreach ($productOptions as $attribute => $option) {
            !$option && $option = '--';
            $value .= '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
        }

        $value .= '</div>';

        return $value;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        if ($row->getData('defected_messages')) {
            $defectedMessages = Mage::helper('M2ePro')->jsonDecode($row->getData('defected_messages'));

            $msg = '';
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
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</span>
HTML;
        }

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $value,
            $marketplaceId
        );

        $parentAsinHtml = '';
        $variationParentId = $row->getData('variation_parent_id');
        if (!empty($variationParentId)) {
            $parentAsinHtml = '<br/><span style="display: block;
                                                margin-bottom: 5px;
                                                font-size: 10px;
                                                color: grey;">'.
                Mage::helper('M2ePro')->__(
                    'child ASIN/ISBN<br/>of parent %parent_asin%',
                    $this->getParentAsin($row->getData('id'))
                ) . '</span>';
        }

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == 1) {
            $generalIdOwnerHtml = '<span style="font-size: 10px; color: grey; display: block;">'.
                                   Mage::helper('M2ePro')->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        return <<<HTML
<a href="{$url}" target="_blank">{$value}</a>{$parentAsinHtml}{$generalIdOwnerHtml}
HTML;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((bool)$row->getData('is_afn_channel')) {
            $sku = $row->getData('amazon_sku');

            if (empty($sku)) {
                return Mage::helper('M2ePro')->__('AFN');
            }

            $productId = $row->getData('id');
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

            $afn = Mage::helper('M2ePro')->__('AFN');
            $total = Mage::helper('M2ePro')->__('Total');
            $inStock = Mage::helper('M2ePro')->__('In Stock');
            $accountId = $listingProduct->getListing()->getAccountId();

            return <<<HTML
<div id="m2ePro_afn_qty_value_{$productId}">
    <span class="m2ePro-online-sku-value" productId="{$productId}" style="display: none">{$sku}</span>
    <span class="m2epro-empty-afn-qty-data" style="display: none">{$afn}</span>
    <div class="m2epro-afn-qty-data" style="display: none">
        <div class="total">{$total}: <span></span></div>
        <div class="in-stock">{$inStock}: <span></span></div>
    </div>
    <a href="javascript:void(0)"
        onclick="AmazonListingAfnQtyHandlerObj.showAfnQty(this,'{$sku}',{$productId}, {$accountId})">
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

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $onlinePrice = $row->getData('online_regular_price');

        $repricingHtml ='';

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() &&
            (bool)(int)$row->getData('is_repricing')) {
            $image = 'money';
            $text = Mage::helper('M2ePro')->__(
                'This Product is used by Amazon Repricing Tool, so its Price cannot be managed via M2E Pro. <br>
                 <strong>Please note</strong> that the Price value(s) shown in the grid might
                 be different from the actual one from Amazon. It is caused by the delay
                 in the values updating made via the Repricing Service'
            );

            if ((int)$row->getData('is_repricing_disabled') == 1 || (int)$row->getData('is_repricing_inactive') == 1) {
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

        $onlineBusinessPrice = $row->getData('online_business_price');

        if (($onlinePrice === null || $onlinePrice === '') &&
            ($onlineBusinessPrice === null || $onlineBusinessPrice === '')
        ) {
            if ($row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
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

        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlinePrice);
        }

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            !$row->getData('is_repricing_inactive')) {
            $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
            $accountId = $listingData['account_id'];
            $sku = $row->getData('amazon_sku');

            $priceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceHandlerObj.showRepricingPrice()">
    {$priceValue}</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_regular_sale_price');
        if ((float)$salePrice > 0) {
            $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d 00:00:00'));

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
                    $salePrice < (float)$onlinePrice
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'.$priceValue.'</span>' .
                                    $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.$salePriceValue;
                } else {
                    $resultHtml .= $priceValue . $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.
                        '<span style="color:gray;">'.'&nbsp;'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $priceValue . $repricingHtml;
        }

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
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message tip-left" style="display:none;
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
        switch ($row->getData('amazon_status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $value .= $this->getViewLogIconHtml($row->getData('id'));

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row->getData('id'));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $value .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $value .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
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
                    $value .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                              .' is Scheduled...]</span>';
                } else {
                    $value .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $value .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $value .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        foreach ($tempLocks as $lock) {
            switch ($lock->getTag()) {
                case 'list_action':
                    $value .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $value .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $value .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $value .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'switch_to_afn_action':
                    $value .= '<br/><span style="color: #605fff">[Switch to AFN in Progress...]</span>';
                    break;

                case 'switch_to_mfn_action':
                    $value .= '<br/><span style="color: #605fff">[Switch to MFN in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $value;
    }

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

    // ---------------------------------------

    public function getViewLogIconHtml($listingProductId)
    {
        $listingProductId = (int)$listingProductId;

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

    public function getEmptyText()
    {
        return Mage::helper('M2ePro')->__(
            'Only Simple and Child Products listed on Amazon will be shown in Seller Сentral View Mode.'
        );
    }

    //########################################

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

    //########################################

    protected function getParentAsin($childId)
    {
        if ($this->_parentAsins === null) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_amazon_listing_product');

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id','variation_parent_id'))
                ->where('listing_product_id IN (?)', $this->getCollection()->getAllIds())
                ->where('variation_parent_id IS NOT NULL');

            $parentIds = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id', 'general_id'))
                ->where('listing_product_id IN (?)', $parentIds);

            $parentAsins = Mage::getResourceModel('core/config')
                ->getReadConnection()
                ->fetchPairs($select);

            $this->_parentAsins = array();
            foreach ($parentIds as $childId => $parentId) {
                $this->_parentAsins[$childId] = $parentAsins[$parentId];
            }
        }

        return $this->_parentAsins[$childId];
    }

    //########################################
}
