<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingSettingsGrid');
        //------------------------------
    }

    // ####################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    // ####################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    // ####################################

    protected function _prepareMassactionItems()
    {
        parent::_prepareMassactionItems();

        $this->getMassactionBlock()->addItem('removeItem', array(
            'label'    => Mage::helper('M2ePro')->__('Remove Item(s)'),
            'url'      => '',
        ), 'other');

        return $this;
    }

    // ####################################

    protected function getGridHandlerJs()
    {
        return 'EbayListingProductAddSettingsGridHandler';
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingProductIds = $this->getListing()->getData('product_add_ids');
        $listingProductIds = array_filter((array)json_decode($listingProductIds));
        $listingProductIds = empty($listingProductIds) ? 0 : implode(',',$listingProductIds);

        //--------------------------------
        // Get collection
        //----------------------------
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        //--------------------------------

        // Join listing product tables
        //----------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.id IN ('.$listingProductIds.')'
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id',

                'template_category_id'  => 'template_category_id',

                'template_payment_mode'  => 'template_payment_mode',
                'template_shipping_mode' => 'template_shipping_mode',
                'template_return_mode'   => 'template_return_mode',

                'template_description_mode'     => 'template_description_mode',
                'template_selling_format_mode'  => 'template_selling_format_mode',
                'template_synchronization_mode' => 'template_synchronization_mode',
            )
        );
        //----------------------------

//        exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumns();

        $this->addColumnAfter('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ), 'product_id');

        return parent::_prepareColumns();
    }

    // ####################################

    protected function getGroupOrder()
    {
        return array(
            'edit_general_settings' => Mage::helper('M2ePro')->__('Edit Settings'),
            'other'                 => Mage::helper('M2ePro')->__('Other')
        );
    }

    protected function getColumnActionsItems()
    {
        $actions = parent::getColumnActionsItems();
        $actions['removeItem'] = array(
            'caption' => Mage::helper('M2ePro')->__('Remove Item'),
            'group'   => 'other',
            'field'   => 'id',
            'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'removeItemAction\']'
        );

        return $actions;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing_productAdd/stepTwoGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        //------------------------------
        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_ebay_listing_autoAction',
            array('listing_id' => $this->getListing()->getId())
        );

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array('step' => 1, '_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/delete';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $path = 'adminhtml_ebay_listing_productAdd/validate';
        $urls[$path] = $this->getUrl('*/' . $path, array('_current' => true));

        $urls = json_encode($urls);
        //------------------------------

        $helper = Mage::helper('M2ePro');
        //------------------------------
        $translations = json_encode(array(
            'Auto Add/Remove Rules'                    => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories'              => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.'     => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.'),
        ));
        //------------------------------

        $js = <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    ListingAutoActionHandlerObj = new EbayListingAutoActionHandler();
</script>
HTML;

        return parent::_toHtml() . $js;
    }

    // ####################################

    /**
     * @inheritdoc
    **/
    protected function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Listing', $this->getRequest()->getParam('listing_id')
            );
        }

        return $this->listing;
    }

    // ####################################
}