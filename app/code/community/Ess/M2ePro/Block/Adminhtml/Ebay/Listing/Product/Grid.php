<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    protected $_sessionKey = 'ebay_listing_product_add';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingProductGrid' . $this->getListing()->getId());

        $this->_hideMassactionDropDown = true;
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection
            ->setListing($this->getListing()->getId())
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

        $collection->getSelect()->distinct();

        // Set filter store
        // ---------------------------------------
        $store = $this->_getStore();

        if ($store->getId()) {
            $collection->joinAttribute(
                'name', 'catalog_product/name', 'entity_id', null, 'left', 0
            );
            $collection->joinAttribute(
                'price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId()
            );
            $collection->joinAttribute(
                'status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', 0
            );
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }

        $prefix = Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix');

        $hideParam = Mage::helper('M2ePro/Data_Session')->getValue($prefix);

        if ($hideParam === null) {
            $hideParam = true;
        }

        $listingId = $this->getListing()->getId();
        if ($hideParam || isset($listingId)) {
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

                $dbExcludeSelect->where('`l`.`account_id` = ?', $this->getListing()->getAccountId());
                $dbExcludeSelect->where('`l`.`marketplace_id` = ?', $this->getListing()->getMarketplaceId());
                $dbExcludeSelect->where('`l`.`component_mode` = ?', Ess_M2ePro_Helper_Component_Ebay::NICK);
            } else {
                $dbExcludeSelect->where('`listing_id` = ?', (int)$this->getListing()->getId());
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

        $collection->addFieldToFilter(
            array(
                array(
                    'attribute' => 'type_id',
                    'in' => Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes()
                ),
            )
        );

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'       => Mage::helper('M2ePro')->__('ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'store_id'     => $this->getListing()->getStoreId(),
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'         => Mage::helper('M2ePro')->__('Title'),
                'align'          => 'left',
                'type'           => 'text',
                'index'          => 'name',
                'filter_index'   => 'name',
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
                'header'       => Mage::helper('M2ePro')->__('Stock Availability'),
                'align'        => 'left',
                'width'        => '90px',
                'type'         => 'options',
                'sortable'     => false,
                'index'        => 'is_in_stock',
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
                'header'       => Mage::helper('M2ePro')->__('SKU'),
                'align'        => 'left',
                'width'        => '90px',
                'type'         => 'text',
                'index'        => 'sku',
                'filter_index' => 'sku'
            )
        );

        $store = $this->_getStore();

        $this->addColumn(
            'price', array(
                'header'         => Mage::helper('M2ePro')->__('Price'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'price',
                'currency_code'  => $store->getBaseCurrency()->getCode(),
                'index'          => 'price',
                'filter_index'   => 'price',
                'frame_callback' => array($this, 'callbackColumnPrice')
            )
        );

        $this->addColumn(
            'qty', array(
                'header'         => Mage::helper('M2ePro')->__('QTY'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'number',
                'index'          => 'qty',
                'filter_index'   => 'qty',
                'frame_callback' => array($this, 'callbackColumnQty')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        $this->getMassactionBlock()->addItem(
            'attributes', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => $this->getUrl('*/adminhtml_listing/massStatus', array('_current'=>true)),
            )
        );

        return parent::_prepareMassaction();
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
        $listingData = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing', $this->getRequest()->getParam('listing_id'))
            ->getData();

        // Get store filter
        // ---------------------------------------
        $storeId = 0;
        if (isset($listingData['store_id'])) {
            $storeId = (int)$listingData['store_id'];
        }

        // ---------------------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        $listingAdditionalData = $this->getListing()->getData('additional_data');
        $listingAdditionalData = Mage::helper('M2ePro')->jsonDecode($listingAdditionalData);

        // ---------------------------------------
        $urls = array_merge(
            $helper->getControllerActions(
                'adminhtml_ebay_listing_autoAction',
                array(
                    'listing_id' => $this->getListing()->getId()
                )
            ),
            $helper->getControllerActions('adminhtml_ebay_category', array('_current' => true))
        );

        $path = 'adminhtml_ebay_listing_productAdd/add';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/setShowSettingsStep';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/setAutoActionPopupShown';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true, 'step' => null));

        $params = array(
            'step'       => null,
            'listing_id' => $this->getListing()->getId()
        );
        if ((bool)$this->getRequest()->getParam('wizard')) {
            $params['wizard'] = true;
        }

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, $params);

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        // ---------------------------------------
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Category Settings' => Mage::helper('M2ePro')->__('Category Settings'),
                'Specifics' => Mage::helper('M2ePro')->__('Specifics'),
                'Auto Add/Remove Rules' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'Based on Magento Categories' => Mage::helper('M2ePro')->__('Based on Magento Categories'),
                'You must select at least 1 Category.' =>
                    Mage::helper('M2ePro')->__('You must select at least 1 Category.'),
                'Rule with the same Title already exists.' =>
                    Mage::helper('M2ePro')->__('Rule with the same Title already exists.'),
                'Listing Settings Customization' => Mage::helper('M2ePro')->__('Listing Settings Customization'),
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $showAutoActionPopup = Mage::helper('M2ePro')->jsonEncode(
            !Mage::helper('M2ePro/Module')->getRegistry()->getValue('/ebay/listing/autoaction_popup/is_shown/')
        );

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $this->getListing()->getId());

        if (isset($sessionData['show_settings_step'])) {
            $showSettingsStep  = (bool)$sessionData['show_settings_step'];
        } elseif (isset($listingAdditionalData['show_settings_step'])) {
            $showSettingsStep  = (bool)$listingAdditionalData['show_settings_step'];
        } else {
            $showSettingsStep  = true;
        }

        $showSettingsPopup = !isset($listingAdditionalData['show_settings_step']);

        $showSettingsStep  = Mage::helper('M2ePro')->jsonEncode($showSettingsStep);
        $showSettingsPopup = Mage::helper('M2ePro')->jsonEncode($showSettingsPopup);

        // ---------------------------------------

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {

        WrapperObj = new AreaWrapper('add_products_container');
        ProgressBarObj = new ProgressBar('add_products_progress_bar');

        ListingProductAddObj = new EbayListingProductAdd({
            show_settings_step: {$showSettingsStep},
            show_settings_popup: {$showSettingsPopup},
            show_autoaction_popup: {$showAutoActionPopup},

            get_selected_products: {$this->getSelectedProductsCallback()}
        });

        ListingAutoActionObj = new EbayListingAutoAction();

        VideoTutorialObj = new VideoTutorial(
            'video_tutorial_pop_up',
            '{$helper->escapeJs($helper->__('eBay/Magento Integration: Products Management'))}',
            function() {}
        );

        VideoTutorialObj.closeCallback = function() { return true; }

    });

</script>
HTML;

        return parent::_toHtml().$js;
    }

    //########################################

    abstract protected function getSelectedProductsCallback();

    //########################################

    protected function getListing()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        return $listing;
    }

    //########################################
}
