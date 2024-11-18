<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    /** @var Ess_M2ePro_Model_Account */
    protected $_account = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->_account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Account',
            $this->_listing->getAccountId()
        );

        $this->setId('amazonListingViewGrid' . $this->_listing->getId());

        $this->_showAdvancedFilterProductsOption = false;
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
        $collection->setListing($this->_listing->getId());
        $collection->setStoreId($this->_listing->getStoreId());

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
                'template_shipping_id'                 => 'template_shipping_id',
                'template_product_type_id'              => 'template_product_type_id',
                'template_product_tax_code_id'         => 'template_product_tax_code_id',
                'general_id'                           => 'general_id',
                'general_id_search_info'               => 'general_id_search_info',
                'search_settings_status'               => 'search_settings_status',
                'search_settings_data'                 => 'search_settings_data',
                'variation_child_statuses'             => 'variation_child_statuses',
                'amazon_sku'                           => 'sku',
                'online_qty'                           => 'online_qty',
                'online_regular_price'                 => 'online_regular_price',
                'online_regular_sale_price'            => 'IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  NULL
                )',
                'current_online_price'                 => 'IF(
                    `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                    `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                    `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                    `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                    `alp`.`online_regular_sale_price`,
                    `alp`.`online_regular_price`
                )',
                'online_regular_sale_price_start_date' => 'online_regular_sale_price_start_date',
                'online_regular_sale_price_end_date'   => 'online_regular_sale_price_end_date',
                'is_repricing'                         => 'is_repricing',
                'is_afn_channel'                       => 'is_afn_channel',
                'is_general_id_owner'                  => 'is_general_id_owner',
                'is_variation_parent'                  => 'is_variation_parent',
                'defected_messages'                    => 'defected_messages',
                'variation_parent_afn_state'           => 'variation_parent_afn_state',
                'variation_parent_repricing_state'     => 'variation_parent_repricing_state',
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->joinTable(
            array('td' => 'M2ePro/Amazon_Template_ProductType'),
            'id=template_product_type_id',
            array(
                'template_product_type_title' => 'title'
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('ts' => 'M2ePro/Amazon_Template_Shipping'),
            'id=template_shipping_id',
            array(
                'template_shipping_title' => 'title'
            ),
            null,
            'left'
        );

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $this->_account->getChildObject();

        if ($amazonAccount->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $amazonAccount->isVatCalculationServiceEnabled()
        ) {
            $collection->joinTable(
                array('tptc' => 'M2ePro/Amazon_Template_ProductTaxCode'),
                'id=template_product_tax_code_id',
                array(
                    'template_product_tax_code_title' => 'title'
                ),
                null,
                'left'
            );
        }

        if ($this->isFilterOrSortByPriceIsUsed(null, 'amazon_online_price')) {
            $collection->joinIndexerParent();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $marketplace = $this->_account->getChildObject()->getMarketplace();

        $this->addColumn(
            'product_id',
            array(
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
            'name',
            array(
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
            'sku',
            array(
                'header'                 => Mage::helper('M2ePro')->__('SKU'),
                'align'                  => 'left',
                'width'                  => '150px',
                'type'                   => 'text',
                'index'                  => 'amazon_sku',
                'filter_index'           => 'amazon_sku',
                'show_defected_messages' => false,
                'renderer'               => 'M2ePro/adminhtml_amazon_grid_column_renderer_sku'
            )
        );

        $this->addColumn(
            'general_id',
            array(
                'header'                    => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'                     => 'left',
                'width'                     => '140px',
                'type'                      => 'text',
                'index'                     => 'general_id',
                'filter_index'              => 'general_id',
                'filter'                    => 'M2ePro/adminhtml_amazon_grid_column_filter_generalId',
                'frame_callback'            => array($this, 'callbackColumnGeneralId'),
                'filter_condition_callback' => array($this, 'callbackFilterGeneralId')
            )
        );

        $this->addColumn(
            'product_type_template',
            array(
                'header'         => Mage::helper('M2ePro')->__('Product Type'),
                'align'          => 'left',
                'width'          => '170px',
                'type'           => 'text',
                'index'          => 'template_product_type_title',
                'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_productType',
                'filter_index' => 'template_product_type_title',
                'filter_condition_callback' => array($this, 'callbackFilterProductTypeSettings'),
                'frame_callback' => array($this, 'callbackColumnTemplateProductType')
            )
        );

        $this->addColumn(
            'shipping_template',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Shipping Policy'),
                'align'                     => 'left',
                'width'                     => '170px',
                'type'                      => 'text',
                'index'                     => 'template_shipping_title',
                'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_policySettings',
                'filter_index'              => 'template_shipping_title',
                'filter_condition_callback' => array($this, 'callbackFilterShippingSettings'),
                'frame_callback'            => array($this, 'callbackColumnTemplateShipping')
            )
        );

        if ($marketplace->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->_account->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $this->addColumn(
                'product_tax_code_template',
                array(
                    'header'         => Mage::helper('M2ePro')->__('Product Tax Code Policy'),
                    'align'          => 'left',
                    'width'          => '170px',
                    'type'           => 'text',
                    'index'          => 'template_product_tax_code_title',
                    'filter_index'   => 'template_product_tax_code_title',
                    'frame_callback' => array($this, 'callbackColumnTemplateProductTaxCode')
                )
            );
        }

        $this->addColumn(
            'actions',
            array(
                'header'      => Mage::helper('M2ePro')->__('Actions'),
                'align'       => 'left',
                'width'       => '100px',
                'type'        => 'action',
                'index'       => 'actions',
                'filter'      => false,
                'sortable'    => false,
                'renderer'    => 'M2ePro/adminhtml_grid_column_renderer_action',
                'field'       => 'id',
                'group_order' => $this->getGroupOrder(),
                'actions'     => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function getGroupOrder()
    {
        $marketplace = $this->_account->getChildObject()->getMarketplace();

        $groups = array(
            'edit_template_product_type' => Mage::helper('M2ePro')->__('Product Type'),
            'edit_template_shipping'    => Mage::helper('M2ePro')->__('Shipping Policy'),
            'other'                     => Mage::helper('M2ePro')->__('Other')
        );

        if ($marketplace->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->_account->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $groups['edit_template_product_tax_code'] = Mage::helper('M2ePro')->__('Product Tax Code Policy');
        }

        return $groups;
    }

    protected function getColumnActionsItems()
    {
        $marketplace = $this->_account->getChildObject()->getMarketplace();

        $helper = Mage::helper('M2ePro');

        $actions = array(
            'assignTemplateProductType' => array(
                'caption'        => $helper->__('Assign'),
                'group'          => 'edit_template_product_type',
                'field'          => 'id',
                'onclick_action' => 'ListingGridObj.actions[\'assignTemplateProductTypeIdAction\']'
            ),

            'unassignTemplateProductType' => array(
                'caption'        => $helper->__('Unassign'),
                'group'          => 'edit_template_product_type',
                'field'          => 'id',
                'onclick_action' => 'ListingGridObj.unassignTemplateProductTypeIdActionConfrim'
            )
        );

        $actions['assignTemplateShipping'] = array(
            'caption'        => $helper->__('Assign'),
            'group'          => 'edit_template_shipping',
            'field'          => 'id',
            'onclick_action' => 'ListingGridObj.actions[\'assignTemplateShippingIdAction\']'
        );

        $actions['unassignTemplateShipping'] = array(
            'caption'        => $helper->__('Unassign'),
            'group'          => 'edit_template_shipping',
            'field'          => 'id',
            'onclick_action' => 'ListingGridObj.unassignTemplateShippingIdActionConfrim'
        );

        if ($marketplace->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->_account->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $actions['assignTemplateProductTaxCode'] = array(
                'caption'        => $helper->__('Assign'),
                'group'          => 'edit_template_product_tax_code',
                'field'          => 'id',
                'onclick_action' => 'ListingGridObj.actions[\'assignTemplateProductTaxCodeIdAction\']'
            );

            $actions['unassignTemplateProductTaxCode'] = array(
                'caption'        => $helper->__('Unassign'),
                'group'          => 'edit_template_product_tax_code',
                'field'          => 'id',
                'onclick_action' => 'ListingGridObj.unassignTemplateProductTaxCodeIdActionConfrim'
            );
        }

        $actions['remapProduct'] = array(
            'caption'            => $helper->__('Link to another Magento Product'),
            'group'              => 'other',
            'field'              => 'id',
            'only_remap_product' => true,
            'onclick_action'     => 'ListingGridObj.actions[\'remapProductAction\']'
        );

        return $actions;
    }

    //########################################

    protected function _prepareMassaction()
    {
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = $this->_account->getChildObject()->getMarketplace();

        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $groups = array(
            'product_type'             => Mage::helper('M2ePro')->__('Product Type'),
            'shipping_policy'                => Mage::helper('M2ePro')->__('Shipping Policy'),
            'edit_template_product_tax_code' => Mage::helper('M2ePro')->__('Product Tax Code Policy'),
            'other'                          => Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem(
            'assignTemplateProductTypeId',
            array(
                'label'   => Mage::helper('M2ePro')->__('Assign'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'product_type'
        );

        $this->getMassactionBlock()->addItem(
            'unassignTemplateProductTypeId',
            array(
                'label'   => Mage::helper('M2ePro')->__('Unassign'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'product_type'
        );

        $this->getMassactionBlock()->addItem(
            'assignTemplateShippingId',
            array(
                'label'   => Mage::helper('M2ePro')->__('Assign'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'shipping_policy'
        );

        $this->getMassactionBlock()->addItem(
            'unassignTemplateShippingId',
            array(
                'label'   => Mage::helper('M2ePro')->__('Unassign'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'shipping_policy'
        );

        if ($marketplace->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->_account->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $this->getMassactionBlock()->addItem(
                'assignTemplateProductTaxCodeId',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Assign'),
                    'url'     => '',
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                'edit_template_product_tax_code'
            );

            $this->getMassactionBlock()->addItem(
                'unassignTemplateProductTaxCodeId',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Unassign'),
                    'url'     => '',
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                'edit_template_product_tax_code'
            );
        }

        $this->getMassactionBlock()->addItem(
            'moving',
            array(
                'label'   => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );

        $this->getMassactionBlock()->addItem(
            'duplicate',
            array(
                'label'   => Mage::helper('M2ePro')->__('Duplicate'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );

        $this->getMassactionBlock()->addItem(
            'transferring',
            array(
                'label'   => Mage::helper('M2ePro')->__('Sell on Another Marketplace'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>' . $productTitle . '</span>';

        $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>' . Mage::helper('M2ePro')->__('SKU') .
            ':</strong> ' . Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

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
                    } else {
                        if (in_array($attribute, array_keys($virtualChannelAttributes))) {
                            $attributesStr .= '<span>' . $attribute .
                                ' (' . $virtualChannelAttributes[$attribute] . ')</span>, ';
                        } else {
                            $attributesStr .= $attribute . ', ';
                        }
                    }
                }

                $attributesStr = rtrim($attributesStr, ', ');
            }

            $value .= $attributesStr;

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }

            $value .= '</div>';
        }

        return $value;
    }

    // ---------------------------------------

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            if ($row->getData('is_general_id_owner') == 1) {
                return Mage::helper('M2ePro')->__('New ASIN/ISBN');
            }

            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    protected function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">' . Mage::helper('M2ePro')->__('receiving...') . '</i>';
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {
            $tip = Mage::helper('M2ePro')->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $iconPath . 'processing.gif';

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt=""></a>
HTML;
        }

        // ---------------------------------------

        return Mage::helper('M2ePro')->__('N/A');
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
            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">' .
                Mage::helper('M2ePro')->__('creator of ASIN/ISBN') .
                '</span>';
        }

        if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
        }

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

        return $text . $generalIdOwnerHtml;
    }

    // ---------------------------------------

    public function callbackColumnTemplateProductType($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_product_type_id')) {
            $url = $this->getUrl(
                '*/adminhtml_amazon_productTypes/edit',
                array(
                    'id' => $row->getData('template_product_type_id')
                )
            );

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_product_type_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        }

        return $html;
    }

    public function callbackColumnTemplateShipping($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_shipping_id')) {
            $url = $this->getUrl(
                '*/adminhtml_amazon_template_shipping/edit',
                array(
                    'id' => $row->getData('template_shipping_id')
                )
            );

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_shipping_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        } elseif ($this->_listing->getData('template_shipping_id')) {
            $shippingSettings = Mage::helper('M2ePro')->__('Use from Listing Settings');

            return <<<HTML
<div style="padding: 4px">
    <span style="color: #666666">{$shippingSettings}</span><br/>
</div>
HTML;
        }

        return $html;
    }

    public function callbackColumnTemplateProductTaxCode($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_product_tax_code_id')) {
            $url = $this->getUrl(
                '*/adminhtml_amazon_template_productTaxCode/edit',
                array(
                    'id' => $row->getData('template_product_tax_code_id')
                )
            );

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_product_tax_code_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        }

        return $html;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku', 'like' => '%' . $value . '%'),
                array('attribute' => 'name', 'like' => '%' . $value . '%')
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

    protected function callbackFilterShippingSettings($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
            $collection->addAttributeToFilter('template_shipping_title',  array('like' => '%' . $inputValue . '%'));
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case '0':
                    $collection->addAttributeToFilter('template_shipping_id', array('null' => true));
                    break;
                case '1':
                    $collection->addAttributeToFilter('template_shipping_id', array('notnull' => true));
                    break;
            }
        }
    }

    protected function callbackFilterProductTypeSettings($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
            $collection->addAttributeToFilter('template_product_type_title',  array('like' => '%' . $inputValue . '%'));
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case '0':
                    $collection->addAttributeToFilter('template_product_type_id', array('null' => true));
                    break;
                case '1':
                    $collection->addAttributeToFilter('template_product_type_id', array('notnull' => true));
                    break;
            }
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_listing/viewGrid', array('_current' => true));
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
        AmazonListingTransferringObj = new AmazonListingTransferring({$this->_listing->getId()});

        setTimeout(function() {
            ListingGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################
}
