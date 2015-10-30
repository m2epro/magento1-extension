<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing',$this->getRequest()->getParam('listing_id'));

        $this->setId('ebayListingProductGrid'.$listing->getId());
        // ---------------------------------------

        $this->hideMassactionDropDown = true;
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
        $listing = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing',$this->getRequest()->getParam('listing_id'))
            ->getData();

        // Get collection
        // ---------------------------------------
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
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
        $collection->getSelect()->distinct();
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

        // Hide products others listings
        // ---------------------------------------
        $prefix = Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix');
        is_null($hideParam = Mage::helper('M2ePro/Data_Session')->getValue($prefix)) && $hideParam = true;

        if ($hideParam || isset($listing['id'])) {

            $dbExcludeSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from(Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                    new Zend_Db_Expr('DISTINCT `product_id`'));

            if ($hideParam) {

                $dbExcludeSelect->join(
                    array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                    '`l`.`id` = `listing_id`', NULL
                );

                $dbExcludeSelect->where('`l`.`account_id` = ?', $listing['account_id']);
                $dbExcludeSelect->where('`l`.`marketplace_id` = ?', $listing['marketplace_id']);
                $dbExcludeSelect->where('`l`.`component_mode` = ?',Ess_M2ePro_Helper_Component_Ebay::NICK);

            } else {
                $dbExcludeSelect->where('`listing_id` = ?',(int)$listing['id']);
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

        $types = Mage::getSingleton('catalog/product_type')->getOptionArray();
        unset($types['virtual']);

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options'   => $types
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

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        // Set fake action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem('attributes', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => $this->getUrl('*/adminhtml_listing/massStatus', array('_current'=>true)),
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
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
        $listingData = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing',$this->getRequest()->getParam('listing_id'))
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

        $listingId = (int)$this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = json_decode($listingAdditionalData, true);

        // ---------------------------------------
        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_ebay_listing_autoAction',
            array(
                'listing_id' => $listingId
            )
        );

        $path = 'adminhtml_ebay_listing_productAdd/add';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/setShowSettingsStep';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/setAutoActionPopupShown';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true, 'step' => null));

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true, 'step' => null));

        $urls = json_encode($urls);
        // ---------------------------------------

        // ---------------------------------------
        $translations = json_encode(array(
            'eBay Categories' => Mage::helper('M2ePro')->__('eBay Categories'),
            'of Product' => Mage::helper('M2ePro')->__('of Product'),
            'Specifics' => Mage::helper('M2ePro')->__('Specifics'),
            'Auto Add/Remove Rules' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => Mage::helper('M2ePro')->__('Based on Magento Categories'),
            'You must select at least 1 Category.' =>
                Mage::helper('M2ePro')->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' =>
                Mage::helper('M2ePro')->__('Rule with the same Title already exists.'),
            'Listing Settings Customization' => Mage::helper('M2ePro')->__('Listing Settings Customization'),
        ));
        // ---------------------------------------

        // ---------------------------------------
        $showAutoActionPopup = !Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/advanced/autoaction_popup/', 'shown'
        );
        Mage::helper('M2ePro/View_Ebay')->isSimpleMode() && $showAutoActionPopup = false;
        $showAutoActionPopup = json_encode($showAutoActionPopup);

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue('ebay_listing_product_add');

        if (isset($productAddSessionData['show_settings_step'])) {
            $showSettingsStep  = (bool)$productAddSessionData['show_settings_step'];
        } elseif (isset($listingAdditionalData['show_settings_step'])) {
            $showSettingsStep  = (bool)$listingAdditionalData['show_settings_step'];
        } else {
            $showSettingsStep  = true;
        }

        $showSettingsPopup = !isset($listingAdditionalData['show_settings_step']);

        Mage::helper('M2ePro/View_Ebay')->isSimpleMode() && $showSettingsStep = false;
        Mage::helper('M2ePro/View_Ebay')->isSimpleMode() && $showSettingsPopup = false;
        $showSettingsStep  = json_encode($showSettingsStep);
        $showSettingsPopup = json_encode($showSettingsPopup);

        // ---------------------------------------

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {

        WrapperObj = new AreaWrapper('add_products_container');
        ProgressBarObj = new ProgressBar('add_products_progress_bar');

        ListingProductAddHandlerObj = new EbayListingProductAddHandler({
            show_settings_step: {$showSettingsStep},
            show_settings_popup: {$showSettingsPopup},
            show_autoaction_popup: {$showAutoActionPopup},

            get_selected_products: {$this->getSelectedProductsCallback()}
        });

        ListingAutoActionHandlerObj = new EbayListingAutoActionHandler();

        VideoTutorialHandlerObj = new VideoTutorialHandler(
            'video_tutorial_pop_up',
            '{$helper->escapeJs($helper->__('eBay/Magento Integration: Products Management'))}',
            function() {}
        );

        VideoTutorialHandlerObj.closeCallback = function() { return true; }

    });

</script>
HTML;

        return parent::_toHtml().$js;
    }

    //########################################

    abstract protected function getSelectedProductsCallback();

    //########################################
}