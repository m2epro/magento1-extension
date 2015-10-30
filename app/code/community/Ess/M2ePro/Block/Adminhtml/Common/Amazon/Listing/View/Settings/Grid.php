<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingViewSettingsGrid'.$listingData['id']);
        // ---------------------------------------

        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_view_modeSwitcher'
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
        /* @var $collection Ess_M2ePro_Model_Mysql4_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection->setListingProductModeOn();
        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty'),
                '{{table}}.stock_id=1',
                'left'
            );

        // ---------------------------------------

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
                'template_shipping_override_id'  => 'template_shipping_override_id',
                'template_description_id'        => 'template_description_id',
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_price'                   => 'online_price',
                'online_sale_price'              => 'IF(
                  `alp`.`online_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_sale_price`,
                  NULL
                )',
                'online_sale_price_start_date'   => 'online_sale_price_start_date',
                'online_sale_price_end_date'     => 'online_sale_price_end_date',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
                'defected_messages'              => 'defected_messages',
                'min_online_price'                      => 'IF(
                    (`t`.`variation_min_price` IS NULL),
                    IF(
                      `alp`.`online_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_sale_price`,
                      `alp`.`online_price`
                    ),
                    `t`.`variation_min_price`
                )',
                'max_online_price'                      => 'IF(
                    (`t`.`variation_max_price` IS NULL),
                    IF(
                      `alp`.`online_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_sale_price`,
                      `alp`.`online_price`
                    ),
                    `t`.`variation_max_price`
                )'
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr('(
                SELECT
                    `malp`.`variation_parent_id`,
                    MIN(
                        IF(
                            `malp`.`online_sale_price_start_date` IS NOT NULL AND
                            `malp`.`online_sale_price_end_date` IS NOT NULL AND
                            `malp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                            `malp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                            `malp`.`online_sale_price`,
                            `malp`.`online_price`
                        )
                    ) as variation_min_price,
                    MAX(
                        IF(
                            `malp`.`online_sale_price_start_date` IS NOT NULL AND
                            `malp`.`online_sale_price_end_date` IS NOT NULL AND
                            `malp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                            `malp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                            `malp`.`online_sale_price`,
                            `malp`.`online_price`
                        )
                    ) as variation_max_price
                FROM `'. Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable() .'` as malp
                INNER JOIN `'. Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable() .'` AS `mlp`
                    ON (`malp`.`listing_product_id` = `mlp`.`id`)
                WHERE `mlp`.`status` IN (
                    ' . Ess_M2ePro_Model_Listing_Product::STATUS_LISTED . ',
                    ' . Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED . '
                ) AND `malp`.`variation_parent_id` IS NOT NULL
                GROUP BY `malp`.`variation_parent_id`
            )'),
            'alp.listing_product_id=t.variation_parent_id',
            array(
                'variation_min_price' => 'variation_min_price',
                'variation_max_price' => 'variation_max_price',
            )
        );
        $collection->joinTable(
            array('td' => 'M2ePro/Template_Description'),
            'id=template_description_id',
            array(
                'template_description_title' => 'title'
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('tso' => 'M2ePro/Amazon_Template_ShippingOverride'),
            'id=template_shipping_override_id',
            array(
                'template_shipping_override_title' => 'title'
            ),
            null,
            'left'
        );

        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('M2ePro')->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'amazon_sku',
            'filter_index' => 'amazon_sku',
            'frame_callback' => array($this, 'callbackColumnAmazonSku')
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('description_template', array(
            'header' => Mage::helper('M2ePro')->__('Description Policy'),
            'align' => 'left',
            'width' => '170px',
            'type' => 'text',
            'index' => 'template_description_title',
            'filter_index' => 'template_description_title',
            'frame_callback' => array($this, 'callbackColumnTemplateDescription')
        ));

        $this->addColumn('shipping_override_template', array(
            'header' => Mage::helper('M2ePro')->__('Shipping Override Policy'),
            'align' => 'left',
            'width' => '170px',
            'type' => 'text',
            'index' => 'template_shipping_override_title',
            'filter_index' => 'template_shipping_override_title',
            'frame_callback' => array($this, 'callbackColumnTemplateShippingOverride')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'field' => 'id',
            'group_order' => $this->getGroupOrder(),
            'actions'     => $this->getColumnActionsItems()
        ));

        return parent::_prepareColumns();
    }

    //########################################

    protected function getGroupOrder()
    {
        return array(
            'edit_template_description' => Mage::helper('M2ePro')->__('Description Policy'),
            'edit_template_shipping_override' => Mage::helper('M2ePro')->__('Shipping Override Policy'),
        );
    }

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'assignTemplateDescription' => array(
                'caption' => $helper->__('Assign'),
                'group'   => 'edit_template_description',
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.actions[\'assignTemplateDescriptionIdAction\']'
            ),

            'unassignTemplateDescription' => array(
                'caption' => $helper->__('Unassign'),
                'group'   => 'edit_template_description',
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.unassignTemplateDescriptionIdActionConfrim'
            ),

            'assignTemplateShippingOverride' => array(
                'caption' => $helper->__('Assign'),
                'group'   => 'edit_template_shipping_override',
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.actions[\'assignTemplateShippingOverrideIdAction\']'
            ),

            'unassignTemplateShippingOverride' => array(
                'caption' => $helper->__('Unassign'),
                'group'   => 'edit_template_shipping_override',
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.unassignTemplateShippingOverrideIdActionConfrim'
            )
        );

        return $actions;
    }

    //########################################

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
            'description_policy' => Mage::helper('M2ePro')->__('Description Policy'),
            'shipping_override_policy' => Mage::helper('M2ePro')->__('Shipping Override Policy'),
            'other'              => Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem('assignTemplateDescriptionId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'description_policy');

        $this->getMassactionBlock()->addItem('unassignTemplateDescriptionId', array(
            'label'    => Mage::helper('M2ePro')->__('Unassign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'description_policy');

        $this->getMassactionBlock()->addItem('assignTemplateShippingOverrideId', array(
            'label'    => Mage::helper('M2ePro')->__('Assign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'shipping_override_policy');

        $this->getMassactionBlock()->addItem('unassignTemplateShippingOverrideId', array(
            'label'    => Mage::helper('M2ePro')->__('Unassign'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'shipping_override_policy');

        $this->getMassactionBlock()->addItem('moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');

        $this->getMassactionBlock()->addItem('duplicate', array(
            'label'    => Mage::helper('M2ePro')->__('Duplicate'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = (int)$listingData['store_id'];

        $withoutImageHtml = '<a href="'
            .$this->getUrl('adminhtml/catalog_product/edit',
                array('id' => $value))
            .'" target="_blank">'.$value.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/',
                'show_products_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($value);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $value.'<hr style="border: 1px solid silver; border-bottom: none;"><img src="'.
            $imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$value.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');
        is_null($tempSku)
        && $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

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

        return $value;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if ((!$row->getData('is_variation_parent') &&
                $row->getData('amazon_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {

            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
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

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('amazon_status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
        }

        $iconPath = $this->getSkinUrl('M2ePro/images/search_statuses/');
        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {

            $tip = Mage::helper('M2ePro')->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $iconPath.'processing.gif';

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt="">
</a>
HTML;
        }
        // ---------------------------------------

        return Mage::helper('M2ePro')->__('N/A');
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
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

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = @json_decode($generalIdSearchInfo, true);
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

    public function callbackColumnTemplateDescription($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_description_id')) {

            $url = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
                'id' => $row->getData('template_description_id')
            ));

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_description_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        }

        return $html;
    }

    public function callbackColumnTemplateShippingOverride($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_shipping_override_id')) {

            $url = $this->getUrl('*/adminhtml_common_amazon_template_shippingOverride/edit', array(
                'id' => $row->getData('template_shipping_override_id')
            ));

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_shipping_override_title'));

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
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%')
            )
        );
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_listing/viewGrid', array('_current'=>true));
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

    //########################################
}