<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_NewAsin_Manual_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    private $listing = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('newAsinManualGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection
        // ---------------------------------------
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource())
            ->setStoreId($this->getListing()->getData('store_id'))
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');
        // ---------------------------------------

        // ---------------------------------------
        $listingProductsIds = $this->getListing()
                                   ->getSetting('additional_data', 'adding_new_asin_listing_products_ids');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.listing_id='.(int)$this->getListing()->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'        => 'listing_product_id',
                'template_description_id'   => 'template_description_id'
            )
        );

        $collection->getSelect()->where('lp.id IN (?)', $listingProductsIds);
        $collection->getSelect()->where('elp.search_settings_status != ? OR elp.search_settings_status IS NULL',
            Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS);
        $collection->getSelect()->where('elp.general_id IS NULL');
        // ---------------------------------------

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
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
            'width'     => '400px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterProductTitle')
        ));

        $this->addColumn('description_template', array(
            'header'    => Mage::helper('M2ePro')->__('Description Policy'),
            'align'     => 'left',
            'width'     => '*',
            'sortable'  => false,
            'type'      => 'options',
            'index'     => 'description_template_id',
            'filter_index' => 'description_template_id',
            'options'   => array(
                1 => Mage::helper('M2ePro')->__('Description Policy Selected'),
                0 => Mage::helper('M2ePro')->__('Description Policy Not Selected')
            ),
            'frame_callback' => array($this, 'callbackColumnDescriptionTemplateCallback'),
            'filter_condition_callback' => array($this, 'callbackColumnDescriptionTemplateFilterCallback')
        ));

        $actionsColumn = array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'align'     => 'center',
            'width'     => '130px',
            'type'      => 'text',
            'field'     => 'id',
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array()
        );

        $actions = array(
            array(
                'caption' => Mage::helper('M2ePro')->__('Set Description Policy'),
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.setDescriptionTemplateRowAction'
            ),
            array(
                'caption' => Mage::helper('M2ePro')->__('Reset Description Policy'),
                'field'   => 'id',
                'onclick_action' => 'ListingGridHandlerObj.resetDescriptionTemplateRowAction'
            )
        );

        $actionsColumn['actions'] = $actions;

        $this->addColumn('actions', $actionsColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('listing_product_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('setDescriptionTemplate', array(
            'label' => Mage::helper('M2ePro')->__('Set Description Policy'),
            'url'   => ''
        ));

        $this->getMassactionBlock()->addItem('resetDescriptionTemplate', array(
            'label' => Mage::helper('M2ePro')->__('Reset Description Policy'),
            'url'   => ''
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $productId = (int)$row->getData('entity_id');
        $storeId = (int)$this->listing->getData('store_id');

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        $htmlWithoutThumbnail = '<a href="' . $url . '" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/','show_products_thumbnails');

        if (!$showProductsThumbnails) {
            return $htmlWithoutThumbnail;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $thumbnail = $magentoProduct->getThumbnailImageLink();
        if (is_null($thumbnail)) {
            return $htmlWithoutThumbnail;
        }

        return <<<HTML
<a href="{$url}" target="_blank">
    {$productId}
    <hr style="border: 1px solid silver; border-bottom: none;">
    <img src="{$thumbnail}" />
</a>
HTML;
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }

        $productTitle = Mage::helper('M2ePro')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $sku = $row->getData('sku');

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') .
            ':</strong> '.Mage::helper('M2ePro')->escapeHtml($sku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $productOptions = $listingProduct->getChildObject()->getVariationManager()
            ->getTypeModel()->getProductAttributes();

        $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
        $value .= implode(', ', $productOptions);
        $value .= '</div>';

        return $value;
    }

    public function callbackColumnDescriptionTemplateCallback($value, $row, $column, $isExport)
    {
        $descriptionTemplateId = $row->getData('template_description_id');

        if (empty($descriptionTemplateId)) {
            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $label = Mage::helper('M2ePro')->__('Not Selected');

            return <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="color: gray; font-style: italic;">{$label}</span>
HTML;
        }

        $templateDescriptionEditUrl = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
            'id' => $descriptionTemplateId
        ));

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')
            ->getModel('Template_Description')->load($descriptionTemplateId);

        $title = Mage::helper('M2ePro')->escapeHtml($descriptionTemplate->getData('title'));

        return <<<HTML
<a target="_blank" href="{$templateDescriptionEditUrl}">{$title}</a>
HTML;
    }

    //########################################

    protected function callbackFilterProductTitle($collection, $column)
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

    // ---------------------------------------

    protected function callbackColumnDescriptionTemplateFilterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if ($value) {
            $collection->addFieldToFilter('template_description_id', array('notnull' => null));
        } else {
            $collection->addFieldToFilter('template_description_id', array('null' => null));
        }
    }

    //########################################

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

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->listing;
    }

    //########################################
}