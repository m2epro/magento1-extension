<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_NewAsin_Category_Grid
    extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    private $listing = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('newAsinCategoryGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->prepareDataByCategories();
    }

    //########################################

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');

        $collection->addFieldToFilter(array(
            array('attribute' => 'entity_id', 'in' => array_keys($this->getData('categories_data')))
        ));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn('magento_category', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Category'),
            'align'     => 'left',
            'width'     => '500px',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMagentoCategory')
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
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array()
        );

        $actions = array(
            array(
                'caption' => Mage::helper('M2ePro')->__('Set Description Policy'),
                'field'   => 'entity_id',
                'onclick_action' => 'ListingGridHandlerObj.setDescriptionTemplateByCategoryRowAction'
            ),
            array(
                'caption' => Mage::helper('M2ePro')->__('Reset Description Policy'),
                'field'   => 'entity_id',
                'onclick_action' => 'ListingGridHandlerObj.resetDescriptionTemplateByCategoryRowAction'
            )
        );

        $actionsColumn['actions'] = $actions;

        $this->addColumn('actions', $actionsColumn);

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('setDescriptionTemplateByCategory', array(
            'label' => Mage::helper('M2ePro')->__('Set Description Policy'),
            'url'   => ''
        ));

        $this->getMassactionBlock()->addItem('resetDescriptionTemplateByCategory', array(
            'label' => Mage::helper('M2ePro')->__('Reset Description Policy'),
            'url'   => ''
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnDescriptionTemplateCallback($value, $row, $column, $isExport)
    {
        $categoriesData = $this->getData('categories_data');
        $productsIds = implode(',', $categoriesData[$row->getData('entity_id')]);

        $descriptionTemplatesData = $this->getData('description_templates_data');
        $descriptionTemplatesIds = array();
        foreach ($categoriesData[$row->getData('entity_id')] as $productId) {
            if (empty($descriptionTemplatesIds[$descriptionTemplatesData[$productId]])) {
                $descriptionTemplatesIds[$descriptionTemplatesData[$productId]] = 0;
            }
            $descriptionTemplatesIds[$descriptionTemplatesData[$productId]]++;
        }

        arsort($descriptionTemplatesIds);

        reset($descriptionTemplatesIds);
        $descriptionTemplateId = key($descriptionTemplatesIds);

        if (empty($descriptionTemplateId)) {
            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $label = Mage::helper('M2ePro')->__('Not Selected');

            return <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="color: gray; font-style: italic;">{$label}</span>
<input type="hidden" id="products_ids_{$row->getData('entity_id')}" value="{$productsIds}">
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
<input type="hidden" id="products_ids_{$row->getData('entity_id')}" value="{$productsIds}">
HTML;
    }

    //########################################

    protected function callbackColumnDescriptionTemplateFilterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $filteredProductsCategories = array();
        $filteredListingProductsIds = array();

        $categoriesData = $this->getData('categories_data');
        $descriptionTemplatesIds = $this->getData('description_templates_data');

        foreach ($descriptionTemplatesIds as $listingProductId => $descriptionTemplateId) {
            if ($descriptionTemplateId !== NULL) {
                $filteredListingProductsIds[] = $listingProductId;
            }
        }

        foreach ($categoriesData as $categoryId => $listingProducts) {
            foreach ($filteredListingProductsIds as $listingProductId) {
                if (in_array($listingProductId, $listingProducts)) {
                    $filteredProductsCategories[] = $categoryId;
                }
            }
        }

        $filteredProductsCategories = array_unique($filteredProductsCategories);

        if ($value) {
            $collection->addFieldToFilter('entity_id', array('in' => $filteredProductsCategories));
        } else if (!empty($filteredProductsCategories)) {
            $collection->addFieldToFilter('entity_id', array('nin' => $filteredProductsCategories));
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
        $addErrorJs = '';

        if (count($this->getData('categories_data')) === 0) {
            $msg = Mage::helper('M2ePro')
                ->__('Magento Categories are not specified for Products you are adding.');

            $addErrorJs = <<<JS
MagentoMessageObj['addError']('{$msg}');
$('save_and_go_to_listing_view').addClassName('disabled').onclick = function() {
    return null;
};
JS;

        }

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridHandlerObj != 'undefined') {
        ListingGridHandlerObj.afterInitPage();
    }

    {$addErrorJs}

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
                ->getObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    private function prepareDataByCategories()
    {
        $listingProductsIds = $this->getListing()
                                   ->getSetting('additional_data', 'adding_new_asin_listing_products_ids');

        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('in' => $listingProductsIds));

        $productsIds = array();
        $descriptionTemplatesIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[$item['id']] = $item['product_id'];
            $descriptionTemplatesIds[$item['id']] = $item['template_description_id'];
        }
        $productsIds = array_unique($productsIds);

        $categoriesIds = Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $this->getListing()->getStoreId()
        );

        $categoriesData = array();

        foreach ($categoriesIds as $categoryId) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addFieldToFilter('entity_id', array('in' => $productsIds));

            $collection->joinTable(
                array('ccp' => 'catalog/category_product'),
                'product_id=entity_id',
                array('category_id' => 'category_id')
            );
            $collection->addFieldToFilter('category_id', $categoryId);

            $data = $collection->getData();

            foreach ($data as $item) {
                $categoriesData[$categoryId][] = array_search($item['entity_id'], $productsIds);
            }

            $categoriesData[$categoryId] = array_unique($categoriesData[$categoryId]);
        }

        $this->setData('categories_data', $categoriesData);
        $this->setData('description_templates_data', $descriptionTemplatesIds);

        $this->getListing()
            ->setSetting('additional_data', 'adding_new_asin_description_templates_data', $descriptionTemplatesIds);
        $this->getListing()->save();
    }

    //########################################
}