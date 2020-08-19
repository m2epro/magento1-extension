<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_CategoryTemplate_Category_Grid
    extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('categoryTemplateCategoryGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        $this->prepareDataByCategories();
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');

        $collection->addFieldToFilter(
            array(
            array('attribute' => 'entity_id', 'in' => array_keys($this->getData('categories_data')))
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'magento_category', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Category'),
            'align'     => 'left',
            'width'     => '500px',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnMagentoCategory')
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
            'sortable'  => false,
            'filter'    => false,
            'actions'   => array()
        );

        $actions = array(
            array(
                'caption' => Mage::helper('M2ePro')->__('Set Category Policy'),
                'field'   => 'entity_id',
                'onclick_action' => 'ListingGridObj.setCategoryTemplateByCategoryRowAction'
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

        $this->getMassactionBlock()->addItem(
            'setCategoryTemplateByCategory', array(
            'label' => Mage::helper('M2ePro')->__('Set Category Policy'),
            'url'   => ''
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnCategoryTemplateCallback($value, $row, $column, $isExport)
    {
        $categoriesData = $this->getData('categories_data');
        $productsIds = implode(',', $categoriesData[$row->getData('entity_id')]);
        $categoryId = $row->getData('entity_id');

        $templatesData = $this->getListing()->getSetting('additional_data', 'adding_category_templates_data');
        $categoryTemplateId = isset($templatesData[$categoryId]) ? $templatesData[$categoryId] : null;

        if (empty($categoryTemplateId)) {
            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $label = Mage::helper('M2ePro')->__('Not Selected');

            return <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="color: gray; font-style: italic;">{$label}</span>
<input type="hidden" id="products_ids_{$row->getData('entity_id')}" value="{$productsIds}">
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
<input type="hidden" id="products_ids_{$row->getData('entity_id')}" value="{$productsIds}">
HTML;
    }

    //########################################

    protected function callbackColumnCategoryTemplateFilterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $templatesData = $this->getListing()->getSetting('additional_data', 'adding_category_templates_data');
        $filteredProductsCategories = array_keys($templatesData);

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
        $categoriesData = $this->getData('categories_data');
        if (!empty($categoriesData)) {
            $errorMessage = Mage::helper('M2ePro')
                                ->__(
                                    "To proceed, the category data must be specified.
                                Please select a relevant Category Policy for at least one Magento Category."
                                );
            $isNotExistProductsWithCategoryTemplate = (int)$this->isNotExistProductsWithCategoryTemplate(
                $this->getData('category_templates_data')
            );

            $addErrorJs = <<<JS
var button = $('add_products_category_template_category_continue');
if ({$isNotExistProductsWithCategoryTemplate}) {
    button.addClassName('disabled');
    button.disable();
    MessageObj.addError(`{$errorMessage}`);
} else {
    button.removeClassName('disabled');
    button.enable();
    MessageObj.clear('error');
}
JS;
        }

        if ($this->getRequest()->isAjax()) {
            $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof ListingGridObj != 'undefined') {
        ListingGridObj.afterInitPage();
    }

    {$addErrorJs}

</script>
HTML;
            return parent::_toHtml() . $javascriptsMain;
        }

        if (count($this->getData('categories_data')) === 0) {
            $msg = Mage::helper('M2ePro')
                    ->__(
                        'Magento Category is not provided for the products you are currently adding.
                  Please go back and select a different option to assign Channel category to your products.'
                    );

            $addErrorJs .= <<<JS
MessageObj.addError(`{$msg}`);
$('add_products_category_template_category_continue').addClassName('disabled').disable();
JS;
        }

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        ListingGridObj.afterInitPage();
        {$addErrorJs}
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
                                  ->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################

    protected function prepareDataByCategories()
    {
        $listingProductsIds = $this->getListing()
                                   ->getSetting('additional_data', 'adding_listing_products_ids');

        $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $listingProductsIds));

        $productsIds = array();
        $categoryTemplatesIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[$item['id']] = $item['product_id'];
            $categoryTemplatesIds[$item['id']] = $item['template_category_id'];
        }

        $productsIds = array_unique($productsIds);

        $categoriesIds = Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $this->getListing()->getStoreId()
        );

        $categoriesData = array();

        foreach ($categoriesIds as $categoryId) {
            /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
            $collection = Mage::getConfig()->getModelInstance(
                'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
                Mage::getModel('catalog/product')->getResource()
            );

            $collection->setListing($this->getListing());
            $collection->setStoreId($this->getListing()->getStoreId());

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
        $this->setData('category_templates_data', $categoryTemplatesIds);
    }

    //########################################

    protected function isNotExistProductsWithCategoryTemplate($categoryTemplatesData)
    {
        if (empty($categoryTemplatesData)) {
            return true;
        }

        foreach ($categoryTemplatesData as $descriptionTemplateData) {
            if (!empty($descriptionTemplateData)) {
                return false;
            }
        }

        return true;
    }

    //########################################
}
