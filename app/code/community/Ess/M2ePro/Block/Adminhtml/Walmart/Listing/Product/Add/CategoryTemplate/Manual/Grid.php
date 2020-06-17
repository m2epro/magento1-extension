<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_CategoryTemplate_Manual_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('categoryTemplateManualGrid');

        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        )
            ->setStoreId($this->getListing()->getStoreId())
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');

        $listingProductsIds = $this->getListing()
                                   ->getSetting('additional_data', 'adding_listing_products_ids');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.listing_id='.(int)$this->getListing()->getId()
        );
        $collection->joinTable(
            array('wlp' => 'M2ePro/Walmart_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'     => 'listing_product_id',
                'template_category_id'   => 'template_category_id'
            )
        );

        $collection->getSelect()->where('lp.id IN (?)', $listingProductsIds);

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
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
                'store_id' => $this->getListing()->getStoreId(),
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'     => 'left',
                'width'     => '400px',
                'type'      => 'text',
                'index'     => 'name',
                'filter_index' => 'name',
                'frame_callback' => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterProductTitle')
            )
        );

        $this->addColumn(
            'category_template', array(
                'header'    => Mage::helper('M2ePro')->__('Category Policy'),
                'align'     => 'left',
                'width'     => '*',
                'sortable'  => false,
                'type'      => 'options',
                'index'     => 'template_category_id',
                'filter_index' => 'template_category_id',
                'options'   => array(
                    1 => Mage::helper('M2ePro')->__('Category Policy Selected'),
                    0 => Mage::helper('M2ePro')->__('Category Policy Not Selected')
                ),
                'frame_callback' => array($this, 'callbackColumnCategoryTemplateCallback'),
                'filter_condition_callback' => array($this, 'callbackColumnCategoryTemplateFilterCallback')
            )
        );

        $actionsColumn = array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'no_link'   => true,
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
                'caption' => Mage::helper('M2ePro')->__('Set Category Policy'),
                'field'   => 'id',
                'onclick_action' => 'ListingGridObj.setCategoryTemplateRowAction'
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
        $this->getMassactionBlock()->addItem(
            'setCategoryTemplate', array(
            'label' => Mage::helper('M2ePro')->__('Set Category Policy'),
            'url'   => ''
            )
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

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

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $listingProductId = (int)$row->getData('id');
        $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $listingProductId);
        $walmartListingProduct = $listingProduct->getChildObject();

        if (!$walmartListingProduct->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        if ($walmartListingProduct->getVariationManager()->isRelationParentType()) {
            $productAttributes = (array)$walmartListingProduct->getVariationManager()
                    ->getTypeModel()->getProductAttributes();
        } else {
            $productOptions = $walmartListingProduct->getVariationManager()
                    ->getTypeModel()->getProductOptions();
            $productAttributes = !empty($productOptions) ? array_keys($productOptions) : array();
        }

        if (!empty($productAttributes)) {
            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
            $value .= implode(', ', $productAttributes);
            $value .= '</div>';
        }

        return $value;
    }

    public function callbackColumnCategoryTemplateCallback($value, $row, $column, $isExport)
    {
        $categoryTemplateId = $row->getData('template_category_id');

        if (empty($categoryTemplateId)) {
            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $label = Mage::helper('M2ePro')->__('Not Selected');

            return <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="color: gray; font-style: italic;">{$label}</span>
HTML;
        }

        $templateCategoryEditUrl = $this->getUrl(
            '*/adminhtml_walmart_template_category/edit', array(
            'id' => $categoryTemplateId
            )
        );

        /** @var Ess_M2ePro_Model_Walmart_Template_Category $categoryTemplate */
        $categoryTemplate = Mage::getModel('M2ePro/Walmart_Template_Category')->load($categoryTemplateId);

        $title = Mage::helper('M2ePro')->escapeHtml($categoryTemplate->getData('title'));

        return <<<HTML
<a target="_blank" href="{$templateCategoryEditUrl}">{$title}</a>
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

    protected function callbackColumnCategoryTemplateFilterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if ($value) {
            $collection->addFieldToFilter('template_category_id', array('notnull' => null));
        } else {
            $collection->addFieldToFilter('template_category_id', array('null' => null));
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
        $errorMessage = Mage::helper('M2ePro')
            ->__(
                "To proceed, the category data must be specified.
                  Please select a relevant Category Policy for at least one product."
            );
        $isNotExistProductsWithCategoryTemplate = (int)$this->isNotExistProductsWithCategoryTemplate();

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    var button = $('walmart_listing_category_continue_btn');
    if ({$isNotExistProductsWithCategoryTemplate}) {
        button.addClassName('disabled');
        button.disable();
        MessageObj.addError(`{$errorMessage}`);
    } else {
        button.removeClassName('disabled');
        button.enable();
        MessageObj.clear('error');
    }

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        ListingGridObj.afterInitPage();
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')
                                  ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->_listing;
    }

    //########################################

    protected function isNotExistProductsWithCategoryTemplate()
    {
        /** @var Mage_Core_Model_Resource_Db_Collection_Abstract $collection */
        $collection = $this->getCollection();
        $countSelect = clone $collection->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        $countSelect->columns('COUNT(*)');
        $countSelect->where('wlp.template_category_id > 0');

        return !$collection->getConnection()->fetchOne($countSelect);
    }

    //########################################
}
