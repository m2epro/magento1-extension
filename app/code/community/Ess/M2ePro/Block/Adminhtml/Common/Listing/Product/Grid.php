<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setData('component', $this->getListing()->getComponentMode());

        $listingData = $this->getListing()->getData();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingProductGrid'.(isset($listingData['id'])?$listingData['id']:''));
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->isAjax = json_encode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingData = $this->getListing()->getData();

        // Get collection
        // ---------------------------------------
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->joinField('qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField('is_in_stock',
                    'cataloginventory/stock_item',
                    'is_in_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left');

        /*$collection->getSelect()->joinLeft(
            array('cisi' => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
            '(cisi.product_id = e.entity_id) AND (cisi.stock_id = 1)',
            array('qty','is_in_stock')
        );*/
        // ---------------------------------------

        // ---------------------------------------
        $collection->getSelect()->distinct();
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute('custom_name',
                                       'catalog_product/name',
                                       'entity_id',
                                       null,
                                       'inner',
                                       $store->getId());
            $collection->joinAttribute('status',
                                       'catalog_product/status',
                                       'entity_id',
                                       null,
                                       'inner',
                                       $store->getId());
            $collection->joinAttribute('visibility',
                                       'catalog_product/visibility',
                                       'entity_id',
                                       null,
                                       'inner',
                                       $store->getId());
            $collection->joinAttribute('price',
                                       'catalog_product/price',
                                       'entity_id',
                                       null,
                                       'left',
                                       $store->getId());
            $collection->joinAttribute('thumbnail',
                                       'catalog_product/thumbnail',
                                       'entity_id',
                                       null,
                                       'left',
                                       $store->getId());
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
        is_null($hideParam = Mage::helper('M2ePro/Data_Session')->getValue($prefix)) && $hideParam = true;

        if ($hideParam || isset($listingData['id'])) {

            $dbExcludeSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from(Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                    new Zend_Db_Expr('DISTINCT `product_id`'));

            if ($hideParam) {

                $dbExcludeSelect->join(
                    array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                    '`l`.`id` = `listing_id`', NULL
                );

                $dbExcludeSelect->where('`l`.`account_id` = ?', $listingData['account_id']);
                $dbExcludeSelect->where('`l`.`marketplace_id` = ?', $listingData['marketplace_id']);
                $dbExcludeSelect->where('`l`.`component_mode` = ?',$this->getData('component'));

            } else {
                $dbExcludeSelect->where('`listing_id` = ?',(int)$listingData['id']);
            }

            // default sql select
            $collection->getSelect()
                ->joinLeft(array('sq' => $dbExcludeSelect), 'sq.product_id = e.entity_id', array())
                ->where('sq.product_id IS NULL');

            // alternatively sql select (for mysql v.5.1)
            // $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbExcludeSelect->__toString().')');
        }
        // ---------------------------------------

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'type_id','neq'=>'virtual'),
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
        if (isset($tempTypes['virtual'])) {
            unset($tempTypes['virtual']);
        }

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

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set fake action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('attributes', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => $this->getUrl(
                '*/adminhtml_common_' . $this->getData('component') . '_listing/massStatus',
                array('_current' => true)
            ),
        ));
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

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $listingData = $this->getListing()->getData();

        $productId = (int)$value;
        $storeId = (int)$listingData['store_id'];

        $withoutImageHtml = '<a href="'
                            .$this->getUrl('adminhtml/catalog_product/edit',
                                           array('id' => $productId))
                            .'" target="_blank">'
                            .$productId
                            .'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')
                                                        ->getConfig()
                                                        ->getGroupValue('/view/','show_products_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $productId.'<hr/><img src="'.$imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$productId.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

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

        $helper = Mage::helper('M2ePro');

        $isShowRuleBlock = json_encode($this->isShowRuleBlock());

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $createEmptyListingMessage = $helper->escapeJs($helper->__('Are you sure you want to create empty Listing?'));

        $showAdvancedFilterButtonText = $helper->escapeJs($helper->__('Show Advanced Filter'));
        $hideAdvancedFilterButtonText = $helper->escapeJs($helper->__('Hide Advanced Filter'));

        $addProductsUrl = $this->getUrl(
            '*/adminhtml_common_listing_productAdd/addProducts', array(
                'component' => $this->getData('component')
            )
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

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.create_empty_listing_message = '{$createEmptyListingMessage}';
    M2ePro.text.show_advanced_filter = '{$showAdvancedFilterButtonText}';
    M2ePro.text.hide_advanced_filter = '{$hideAdvancedFilterButtonText}';
    M2ePro.url.add_products = '{$addProductsUrl}';
    M2ePro.url.back = '{$backUrl}';

    WrapperObj = new AreaWrapper('add_products_container');
    ProgressBarObj = new ProgressBar('add_products_progress_bar');
    AddListingObj = new CommonListingAddListingHandler(M2ePro, ProgressBarObj, WrapperObj);
    AddListingObj.listing_id = '{$this->getRequest()->getParam('id')}';
    ProductGridHandlerObj = new ListingProductGridHandler(AddListingObj);
    ProductGridHandlerObj.setGridId('{$this->getId()}');

    var init = function () {
        {$this->getId()}JsObject.doFilter = ProductGridHandlerObj.setFilter;
        {$this->getId()}JsObject.resetFilter = ProductGridHandlerObj.resetFilter;
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
                'onclick' => 'ProductGridHandlerObj.advancedFilterToggle()',
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

    private function isShowRuleBlock()
    {
        $ruleData = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('rule_prefix')
        );

        $showHideProductsOption = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix')
        );

        return !empty($ruleData) || is_null($showHideProductsOption) || $showHideProductsOption;
    }

    //########################################

    private function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component')
                ->getCachedUnknownObject('Listing', $listingId)->getChildObject();
        }

        return $this->listing;
    }

    //########################################
}