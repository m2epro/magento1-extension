<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingViewSettingsGrid'.$listingData['id']);
        // ---------------------------------------

        $this->_showAdvancedFilterProductsOption = false;
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
        $collection->setListing($listingData['id']);
        $collection->setStoreId($listingData['store_id']);

        if ($this->isFilterOrSortByPriceIsUsed(null, 'walmart_online_price')) {
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
                'template_category_id'           => 'template_category_id',
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
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->joinTable(
            array('wtc' => 'M2ePro/Walmart_Template_Category'),
            'id=template_category_id',
            array(
                'template_category_title' => 'title',
            ),
            null,
            'left'
        );

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
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
            'width' => '150px',
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
            'width' => '150px',
            'type' => 'text',
            'index' => 'gtin',
            'filter_index' => 'gtin',
            'frame_callback' => array($this, 'callbackColumnGtin'),
            'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'category_template', array(
            'header' => Mage::helper('M2ePro')->__('Category Policy'),
            'align' => 'left',
            'width' => '250px',
            'type' => 'text',
            'index' => 'template_category_title',
            'filter_index' => 'template_category_title',
            'frame_callback' => array($this, 'callbackColumnTemplateCategory')
            )
        );

        $this->addColumn(
            'actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'field' => 'id',
            'no_link'  => true,
            'actions'     => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'assignTemplateDescription' => array(
                'caption' => $helper->__('Use Another Category Policy'),
                'group'   => 'edit_template_category',
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.actions[\'changeTemplateCategoryIdAction\']'
            )
        );

        return $actions;
    }

    //########################################

    protected function _prepareMassaction()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Account', (int)$listingData['account_id']
        );

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = $account->getChildObject()->getMarketplace();

        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $groups = array(
            'category_policy' => Mage::helper('M2ePro')->__('Category Policy'),
            'other'           => Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem(
            'changeTemplateCategoryId', array(
            'label'    => Mage::helper('M2ePro')->__('Use Another'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'category_policy'
        );

        $this->getMassactionBlock()->addItem(
            'moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );

        $this->getMassactionBlock()->addItem(
            'duplicate', array(
            'label'    => Mage::helper('M2ePro')->__('Duplicate'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );
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

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

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

    public function callbackColumnWalmartSku($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    // ---------------------------------------

    public function callbackColumnGtin($gtin, $row, $column, $isExport)
    {
        if (empty($gtin)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $gtinHtml = Mage::helper('M2ePro')->escapeHtml($gtin);
        $channelUrl = $row->getData('channel_url');

        if (!empty($channelUrl)) {
            $gtinHtml = <<<HTML
<a href="{$channelUrl}" target="_blank">{$gtin}</a>
HTML;
        }

        $html = <<<HTML
<div class="walmart-identifiers-gtin">{$gtinHtml}</div>
HTML;

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

        return $html;
    }

    // ---------------------------------------

    public function callbackColumnTemplateCategory($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_category_id')) {
            $url = $this->getUrl(
                '*/adminhtml_walmart_template_category/edit', array(
                'id' => $row->getData('template_category_id')
                )
            );

            $templateTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('template_category_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        }

        return $html;
    }

    public function callbackColumnTemplateProductTaxCode($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_product_tax_code_id')) {
            $url = $this->getUrl(
                '*/adminhtml_walmart_template_productTaxCode/edit', array(
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
