<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Magento_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewGridMagento'.$listing->getId());
        // ---------------------------------------

        $this->hideMassactionColumn = true;
        $this->hideMassactionDropDown = true;
        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    //########################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection
        // ---------------------------------------
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->joinTable(
                array('cisi' => 'cataloginventory/stock_item'),
                'product_id=entity_id',
                array('qty' => 'qty',
                      'is_in_stock' => 'is_in_stock'),
                '{{table}}.stock_id=1',
                'left'
            );
        // ---------------------------------------

        // ---------------------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'ebay_status' => 'status',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$listing->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(online_qty - online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_category'       => 'online_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',
                'min_online_price'      => 'IF(
                    (`t`.`variation_min_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_min_price`
                )',
                'max_online_price'      => 'IF(
                    (`t`.`variation_max_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_max_price`
                )'
            ),
            NULL,
            'left'
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            NULL,
            'left'
        );
        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr('(
                SELECT
                    `mlpv`.`listing_product_id`,
                    MIN(`melpv`.`online_price`) as variation_min_price,
                    MAX(`melpv`.`online_price`) as variation_max_price
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` AS `mlpv`
                INNER JOIN `' .
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable() .
                '` AS `melpv`
                    ON (`mlpv`.`id` = `melpv`.`listing_product_variation_id`)
                WHERE `melpv`.`status` != ' . Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED . '
                GROUP BY `mlpv`.`listing_product_id`
            )'),
            'elp.listing_product_id=t.listing_product_id',
            array(
                'variation_min_price' => 'variation_min_price',
                'variation_max_price' => 'variation_max_price',
            )
        );
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'price', 'catalog_product/price', 'entity_id', NULL, 'left', $store->getId()
            );
            $collection->joinAttribute(
                'status', 'catalog_product/status', 'entity_id', NULL, 'inner',$store->getId()
            );
            $collection->joinAttribute(
                'visibility', 'catalog_product/visibility', 'entity_id', NULL, 'inner',$store->getId()
            );
            $collection->joinAttribute(
                'thumbnail', 'catalog_product/thumbnail', 'entity_id', NULL, 'left',$store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }
        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Title'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $tempTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        unset($tempTypes['virtual']);

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => $tempTypes
        ));

        $this->addColumn('is_in_stock', array(
            'header'    => Mage::helper('M2ePro')->__('Stock Availability'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => array(
                '1' => Mage::helper('M2ePro')->__('In Stock'),
                '0' => Mage::helper('M2ePro')->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnIsInStock')
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('M2ePro')->__('SKU'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'text',
            'index'     => 'sku',
            'filter_index' => 'sku'
        ));

        $store = $this->_getStore();

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'filter_index' => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('M2ePro')->__('QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'qty',
            'filter_index' => 'qty',
            'frame_callback' => array($this, 'callbackColumnQty')
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('M2ePro')->__('Visibility'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'visibility',
            'filter_index' => 'visibility',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('M2ePro')->__('Status'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'status',
            'filter_index' => 'status',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        if (!Mage::app()->isSingleStoreMode()) {

            $this->addColumn('websites', array(
                'header'    => Mage::helper('M2ePro')->__('Websites'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'websites',
                'filter_index' => 'websites',
                'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
            ));
        }

        return parent::_prepareColumns();
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
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

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}