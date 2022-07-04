<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;
use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Filter_CategoryMode as CategoryModeFilter;

/**
 * @method setCategoriesData()
 * @method getCategoriesData()
 */
class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Category_Grid extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategoryGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();

        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter(
            'entity_id',
            array('in' => array_keys($this->getCategoriesData()))
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

        $helper = Mage::helper('M2ePro');
        $this->addColumn(
            'ebay_categories', array(
                'header'    => Mage::helper('M2ePro')->__('eBay Categories'),
                'align'     => 'left',
                'width'     => '*',
                'type'      => 'options',
                'filter'    => 'M2ePro/adminhtml_ebay_grid_column_filter_categoryMode',
                'category_type' => eBayCategory::TYPE_EBAY_MAIN,
                'options'   => array(
                    CategoryModeFilter::MODE_SELECTED     => $helper->__('Primary Category Selected'),
                    CategoryModeFilter::MODE_NOT_SELECTED => $helper->__('Primary Category Not Selected'),
                    CategoryModeFilter::MODE_TITLE        => $helper->__('Primary Category Name/ID')
                ),
                'sortable'                  => false,
                'frame_callback'            => array($this, 'callbackColumnCategories'),
                'filter_condition_callback' => array($this, 'callbackFilterEbayCategories'),
            )
        );

        $this->addColumn(
            'actions', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'center',
                'width'     => '150px',
                'type'      => 'text',
                'sortable'  => false,
                'filter'    => false,
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'actions'   => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        $this->getMassactionBlock()->addItem(
            'editCategories',
            array(
                'label' => Mage::helper('M2ePro')->__('Edit Categories'),
                'url'   => '',
            )
        );

        $this->getMassactionBlock()->addItem(
            'resetCategories',
            array(
                'label' => Mage::helper('M2ePro')->__('Reset Categories'),
                'url'   => '',
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    public function callbackColumnCategories($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_CategoryInfo $renderer */
        $renderer = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_grid_column_renderer_categoryInfo');
        $renderer->setColumn($column);
        $renderer->setCategoriesData($this->getCategoriesData());
        $renderer->setEntityIdField('entity_id');
        $renderer->setListing($this->_listing);

        return $renderer->render($row);
    }

    //########################################

    protected function callbackFilterEbayCategories($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        $categoryType = $column->getData('category_type');

        if ($filter == null || $categoryType === null) {
            return;
        }

        $categoryStat = array(
            'selected'  => array(),
            'blank'     => array(),
            'ebay'      => array(),
            'attribute' => array(),
            'path'      => array()
        );

        foreach ($this->getCategoriesData() as $categoryId => $categoryData) {
            if (!isset($categoryData[$categoryType]) ||
                $categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_NONE
            ) {
                $categoryStat['blank'][] = $categoryId;
                continue;
            }

            $categoryStat['selected'][] = $categoryId;

            if ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_EBAY) {
                $categoryStat['ebay'][] = $categoryId;
            }

            if ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE) {
                $categoryStat['attribute'][] = $categoryId;
            }

            if (!empty($filter['title']) &&
                (strpos($categoryData[$categoryType]['path'], $filter['title']) !== false ||
                 strpos($categoryData[$categoryType]['value'], $filter['title']) !== false)
            ) {
                $categoryStat['path'][] = $categoryId;
            }
        }

        $ids = array();
        $filter['mode'] == CategoryModeFilter::MODE_NOT_SELECTED && $ids = $categoryStat['blank'];
        $filter['mode'] == CategoryModeFilter::MODE_SELECTED     && $ids = $categoryStat['selected'];
        $filter['mode'] == CategoryModeFilter::MODE_EBAY         && $ids = $categoryStat['ebay'];
        $filter['mode'] == CategoryModeFilter::MODE_ATTRIBUTE    && $ids = $categoryStat['attribute'];
        $filter['mode'] == CategoryModeFilter::MODE_TITLE        && $ids = $categoryStat['path'];

        $collection->addFieldToFilter('entity_id', array('in' => $ids));
    }

    //########################################

    protected function getColumnActionsItems()
    {
        return array(
            'editCategories' => array(
                'caption'        => Mage::helper('catalog')->__('Edit Categories'),
                'field'          => 'id',
                'onclick_action' => "EbayListingCategoryCategoryGridObj.actions['editCategoriesAction']"
            ),
            'resetCategories' => array(
                'caption' => Mage::helper('catalog')->__('Reset Categories'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => "EbayListingCategoryCategoryGridObj.actions['resetCategoriesAction']"
            ),
        );
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');
        $urls = array_merge(
            $helper->getControllerActions('adminhtml_ebay_listing_categorySettings', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_category', array('_current' => true)),
            $helper->getControllerActions('adminhtml_ebay_accountStoreCategory')
        );

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
                'step' => 3,
                '_current' => true
            )
        );
        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $translations =  Mage::helper('M2ePro')->jsonEncode(
            array(
                'Set eBay Category'        => $helper->__('Set eBay Category'),
                'Category Settings'        => $helper->__('Category Settings'),
                'Specifics'                => $helper->__('Specifics'),
                'select_relevant_category' => $helper->__(
                    'To proceed, the category data must be specified.
                     Please select a relevant Primary eBay Category for at least one Magento Category.'
                )
            )
        );

        $categoriesData = $this->getCategoriesData();
        $isAlLeasOneCategorySelected = (int)!$this->isAlLeasOneCategorySelected($categoriesData);
        $showErrorMessage = (int)!empty($categoriesData);

        $commonJs = <<<HTML
<script type="text/javascript">

    EbayListingCategoryCategoryGridObj.afterInitPage();
    EbayListingCategoryCategoryGridObj.validateCategories(
        '{$isAlLeasOneCategorySelected}', '{$showErrorMessage}'
    )
    
</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    EbayListingCategoryGridObj = new EbayListingCategoryGrid('{$this->getId()}');
    EbayListingCategoryCategoryGridObj = new EbayListingCategoryCategoryGrid('{$this->getId()}');
</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    protected function isAlLeasOneCategorySelected($categoriesData)
    {
        if (empty($categoriesData)) {
            return false;
        }

        foreach ($categoriesData as $productId => $categoryData) {
            if (isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]) &&
                $categoryData[eBayCategory::TYPE_EBAY_MAIN]['mode'] !== TemplateCategory::CATEGORY_MODE_NONE
            ) {
                if ($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'] !== null) {
                    return true;
                }

                $specificsRequired = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                    $categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'],
                    $this->_listing->getMarketplaceId()
                );

                if (!$specificsRequired) {
                    return true;
                }
            }
        }

        return false;
    }

    //########################################
}
