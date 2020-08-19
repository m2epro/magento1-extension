<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingData = $this->getListing()->getData();

        $this->setId('listingProductGrid'.(isset($listingData['id'])?$listingData['id']:''));

        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->isAjax = Mage::helper('M2ePro')->jsonEncode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingData = $this->getListing()->getData();

        // Get collection
        // ---------------------------------------
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection
            ->setListing($listingData['id'])
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('type_id');

        /**
         * We have to use Admin Store view for collection. Otherwise magento will use index table for price column
         * app/code/core/Mage/Catalog/Model/Resource/Product/Collection.php
         * setOrder() | addAttributeToSort()
         */
        $collection->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection->joinStockItem(
            array(
            'qty' => 'qty',
            'is_in_stock' => 'is_in_stock'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $collection->getSelect()->distinct();
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                0
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                null,
                'left',
                0
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }

        // ---------------------------------------

        // Hide products others listings
        // ---------------------------------------
        $prefix = Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix');

        $hideParam = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if ($hideParam === null) {
            $hideParam = true;
        }

        if ($hideParam || isset($listingData['id'])) {
            $dbExcludeSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from(
                    Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                    new Zend_Db_Expr('DISTINCT `product_id`')
                );

            if ($hideParam) {
                $dbExcludeSelect->join(
                    array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                    '`l`.`id` = `listing_id`', null
                );

                $dbExcludeSelect->where('`l`.`account_id` = ?', $listingData['account_id']);
                $dbExcludeSelect->where('`l`.`marketplace_id` = ?', $listingData['marketplace_id']);
                $dbExcludeSelect->where('`l`.`component_mode` = ?', Ess_M2ePro_Helper_Component_Walmart::NICK);
            } else {
                $dbExcludeSelect->where('`listing_id` = ?', (int)$listingData['id']);
            }

            $useAlternativeSelect = (bool)Mage::helper('M2ePro/Module_Configuration')
                ->getViewProductsGridUseAlternativeMysqlSelectMode();

            if ($useAlternativeSelect) {
                $collection->getSelect()
                    ->where('`e`.`entity_id` NOT IN ('.$dbExcludeSelect->__toString().')');
            } else {
                $collection->getSelect()
                   ->joinLeft(array('sq' => $dbExcludeSelect), 'sq.product_id = e.entity_id', array())
                   ->where('sq.product_id IS NULL');
            }
        }

        // ---------------------------------------

        $collection->addFieldToFilter(
            array(
                array(
                    'attribute' => 'type_id',
                    'in' => Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes()
                ),
            )
        );

        $store->getId() && $collection->setStoreId($store->getId());

        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::helper('M2ePro/Data_Global')->getValue('rule_model');
        $ruleModel->setAttributesFilterToCollection($collection);

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'    => Mage::helper('M2ePro')->__('ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'entity_id',
                'filter_index' => 'entity_id',
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId',
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Title'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'name',
                'filter_index' => 'name',
                'frame_callback' => array($this, 'callbackColumnProductTitle')
            )
        );

        $this->addColumn(
            'type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => Mage::helper('M2ePro/Magento_Product')->getTypesOptionArray()
            )
        );

        $this->addColumn(
            'is_in_stock', array(
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
            )
        );

        $this->addColumn(
            'sku', array(
                'header'    => Mage::helper('M2ePro')->__('SKU'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'text',
                'index'     => 'sku',
                'filter_index' => 'sku'
            )
        );

        $store = $this->_getStore();

        $this->addColumn(
            'price', array(
                'header'    => Mage::helper('M2ePro')->__('Price'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'     => 'price',
                'filter_index' => 'price',
                'frame_callback' => array($this, 'callbackColumnPrice')
            )
        );

        $this->addColumn(
            'qty', array(
                'header'    => Mage::helper('M2ePro')->__('QTY'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'qty',
                'filter_index' => 'qty',
                'frame_callback' => array($this, 'callbackColumnQty')
            )
        );

        $this->addColumn(
            'visibility', array(
                'header'    => Mage::helper('M2ePro')->__('Visibility'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'visibility',
                'filter_index' => 'visibility',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
            )
        );

        $this->addColumn(
            'status', array(
                'header'    => Mage::helper('M2ePro')->__('Status'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'status',
                'filter_index' => 'status',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'websites', array(
                    'header'    => Mage::helper('M2ePro')->__('Websites'),
                    'align'     => 'left',
                    'width'     => '90px',
                    'type'      => 'options',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'filter_index' => 'websites',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
                )
            );
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set fake action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'attributes', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => $this->getUrl(
                '*/adminhtml_walmart_listing/massStatus',
                array('_current' => true)
            ),
            )
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockHtml()
    {
        $advancedFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_product_rule');
        $advancedFilterBlock->setShowHideProductsOption();
        $advancedFilterBlock->setGridJsObjectName($this->getJsObjectName());

        return $advancedFilterBlock->toHtml() . parent::getMassactionBlockHtml();
    }

    //########################################

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    protected function _getStore()
    {
        $listingData = $this->getListing()->getData();

        // Get store filter
        // ---------------------------------------
        $storeId = 0;
        if (isset($listingData['store_id'])) {
            $storeId = (int)$listingData['store_id'];
        }

        // ---------------------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $cssBefore = <<<STYLE
<style type="text/css">
    table.massaction div.right {
        display: none;
    }
</style>
STYLE;

        $isShowRuleBlock = Mage::helper('M2ePro')->jsonEncode($this->isShowRuleBlock());

        $addProductsUrl = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/addProducts'
        );
        $backUrl = $this->getUrl('*/*/index');

        $javascript = <<<HTML
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.url.add_products = '{$addProductsUrl}';
    M2ePro.url.back = '{$backUrl}';

    WrapperObj = new AreaWrapper('add_products_container');
    ProgressBarObj = new ProgressBar('add_products_progress_bar');
    AddProductObj = new WalmartListingProductAdd(M2ePro, ProgressBarObj, WrapperObj);
    AddProductObj.listing_id = '{$this->getRequest()->getParam('id')}';
    ProductGridObj = new ListingProductGrid(AddProductObj);
    ProductGridObj.setGridId('{$this->getId()}');

    var init = function () {
        {$this->getId()}JsObject.doFilter = ProductGridObj.setFilter;
        {$this->getId()}JsObject.resetFilter = ProductGridObj.resetFilter;
        if ({$isShowRuleBlock}) {
            $('listing_product_rules').show();
            if ($('advanced_filter_button')) {
                $('advanced_filter_button').simulate('click');
            }
        }
    }

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);
</script>
HTML;

        return $cssBefore.parent::_toHtml().$javascript;
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!$this->getChild('advanced_filter_button')) {
            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('adminhtml')->__('Show Advanced Filter'),
                'onclick' => 'ProductGridObj.advancedFilterToggle()',
                'class'   => 'task',
                'id'      => 'advanced_filter_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('advanced_filter_button', $buttonBlock);
            // ---------------------------------------
        }

        return $this->getChildHtml('advanced_filter_button');
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        if ($this->getFilterVisibility()) {
            $html.= $this->getResetFilterButtonHtml();
            if (!$this->isShowRuleBlock()) {
                $html.= $this->getAdvancedFilterButtonHtml();
            }

            $html.= $this->getSearchButtonHtml();
        }

        return $html;
    }

    //########################################

    protected function isShowRuleBlock()
    {
        $ruleData = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('rule_prefix')
        );

        $showHideProductsOption = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix')
        );

        return !empty($ruleData) || $showHideProductsOption === null || $showHideProductsOption;
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component')
                                  ->getCachedUnknownObject('Listing', $listingId)->getChildObject();
        }

        return $this->_listing;
    }

    //########################################
}
