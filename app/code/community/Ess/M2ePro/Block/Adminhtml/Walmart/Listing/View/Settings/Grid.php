<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('walmartListingViewGrid' . $this->_listing->getId());

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
            array('wlp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'template_category_id'     => 'template_category_id',
                'variation_child_statuses' => 'variation_child_statuses',
                'walmart_sku'              => 'sku',
                'gtin'                     => 'gtin',
                'upc'                      => 'upc',
                'ean'                      => 'ean',
                'isbn'                     => 'isbn',
                'wpid'                     => 'wpid',
                'item_id'                  => 'item_id',
                'online_qty'               => 'online_qty',
                'online_price'             => 'online_price',
                'is_variation_parent'      => 'is_variation_parent',
                'is_online_price_invalid'  => 'is_online_price_invalid',
                'online_start_date'        => 'online_start_date',
                'online_end_date'          => 'online_end_date',
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

        if ($this->isFilterOrSortByPriceIsUsed(null, 'walmart_online_price')) {
            $collection->joinIndexerParent();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
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
                'header'        => Mage::helper('M2ePro')->__('SKU'),
                'align'         => 'left',
                'width'         => '150px',
                'type'          => 'text',
                'index'         => 'walmart_sku',
                'filter_index'  => 'walmart_sku',
                'show_edit_sku' => false,
                'renderer'      => 'M2ePro/adminhtml_walmart_grid_column_renderer_sku',
            )
        );

        $this->addColumn(
            'gtin',
            array(
                'header'                    => Mage::helper('M2ePro')->__('GTIN'),
                'align'                     => 'left',
                'width'                     => '150px',
                'type'                      => 'text',
                'index'                     => 'gtin',
                'filter_index'              => 'gtin',
                'show_edit_identifier'      => false,
                'marketplace_id'            => $this->_listing->getMarketplaceId(),
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_gtin',
                'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'category_template',
            array(
                'header'         => Mage::helper('M2ePro')->__('Category Policy'),
                'align'          => 'left',
                'width'          => '250px',
                'type'           => 'text',
                'index'          => 'template_category_title',
                'filter_index'   => 'template_category_title',
                'frame_callback' => array($this, 'callbackColumnTemplateCategory')
            )
        );

        $this->addColumn(
            'actions',
            array(
                'header'   => Mage::helper('M2ePro')->__('Actions'),
                'align'    => 'left',
                'width'    => '100px',
                'type'     => 'action',
                'index'    => 'actions',
                'filter'   => false,
                'sortable' => false,
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_action',
                'field'    => 'id',
                'no_link'  => true,
                'group_order' => $this->getGroupOrder(),
                'actions'  => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function getGroupOrder()
    {
        return array(
            'edit_template_category' => Mage::helper('M2ePro')->__('Category Policy'),
            'other' => Mage::helper('M2ePro')->__('Other')
        );
    }

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'assignTemplateDescription' => array(
                'caption'        => $helper->__('Use Another Category Policy'),
                'group'          => 'edit_template_category',
                'field'          => 'id',
                'onclick_action' => 'ListingGridObj.actions[\'changeTemplateCategoryIdAction\']'
            )
        );

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
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $groups = array(
            'category_policy' => Mage::helper('M2ePro')->__('Category Policy'),
            'other'           => Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem(
            'changeTemplateCategoryId',
            array(
                'label'   => Mage::helper('M2ePro')->__('Use Another'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'category_policy'
        );

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

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>' . $productTitle . '</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>' . Mage::helper('M2ePro')->__('SKU') .
            ':</strong> ' . Mage::helper('M2ePro')->escapeHtml($tempSku) . '<br/>';

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
                !$option && $option = '--';
                $value .= '<strong>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option) . '<br/>';
            }

            $value .= '</div>';
        }

        return $value;
    }

    // ---------------------------------------

    public function callbackColumnTemplateCategory($value, $row, $column, $isExport)
    {
        $html = Mage::helper('M2ePro')->__('N/A');

        if ($row->getData('template_category_id')) {
            $url = $this->getUrl(
                '*/adminhtml_walmart_template_category/edit',
                array(
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
                '*/adminhtml_walmart_template_productTaxCode/edit',
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
        return $this->getUrl('*/adminhtml_walmart_listing/viewGrid', array('_current' => true));
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

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################
}
