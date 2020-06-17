<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_ProductsSecondary_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_AbstractGrid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_showAdvancedFilterProductsOption = false;
        $this->setId('ebayConfigurationCategoryViewProductsSecondaryGrid');
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
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'status'          => 'status',
                'component_mode'  => 'component_mode',
                'listing_id'      => 'listing_id',
                'additional_data' => 'additional_data',
            )
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'    => 'listing_product_id',
                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(elp.online_qty - elp.online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_main_category'  => 'online_main_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_bids'           => 'online_bids',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',
                'template_category_secondary_id'  => 'template_category_secondary_id',
            )
        );
        $collection->joinTable(
            array('l' => 'M2ePro/Listing'),
            'id=listing_id',
            array(
                'store_id'       => 'store_id',
                'account_id'     => 'account_id',
                'marketplace_id' => 'marketplace_id',
            )
        );
        $collection->joinTable(
            array('em' => 'M2ePro/Ebay_Marketplace'),
            'marketplace_id=marketplace_id',
            array(
                'currency' => 'currency',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            null,
            'left'
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $templateCategory */
        $templateCategory = Mage::getModel('M2ePro/Ebay_Template_Category')->load(
            $this->getRequest()->getParam('template_id')
        );

        $collection->joinTable(
            array('etc' => 'M2ePro/Ebay_Template_Category'),
            'id=template_category_secondary_id',
            array(
                'category_id'        => 'category_id',
                'category_attribute' => 'category_attribute',
                'is_custom_template' => 'is_custom_template',
            )
        );

        if ($templateCategory->isCategoryModeEbay()) {
            $collection->addFieldToFilter('category_id', $templateCategory->getCategoryId());
        }

        if ($templateCategory->isCategoryModeAttribute()) {
            $collection->addFieldToFilter('category_attribute', $templateCategory->getCategoryAttribute());
        }

        $collection->addFieldToFilter('marketplace_id', $templateCategory->getMarketplaceId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('is_custom_template');
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewSecondaryGrid', array('_current'=>true));
    }

    //########################################
}
