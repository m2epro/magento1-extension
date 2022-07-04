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
class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Manually_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingCategoryManuallyGrid');

        $this->setDefaultSort('entity_id');
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
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('name');

        $collection->getSelect()->distinct();
        $store = Mage::app()->getStore((int)$this->_listing->getData('store_id'));

        if ($store->getId()) {
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('thumbnail');
        }

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.listing_id='.(int)$this->_listing->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id'
            )
        );

        $productAddIds = (array)Mage::helper('M2ePro')->jsonDecode($this->_listing->getData('product_add_ids'));
        $collection->getSelect()->where('lp.id IN (?)', $productAddIds);

        $this->setCollection($collection);
        return parent::_prepareCollection();
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
                'store_id'  => $this->_listing->getStoreId(),
                'filter_index' => 'entity_id',
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId',
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Product Title'),
                'align'     => 'left',
                'width'     => '500px',
                'type'      => 'text',
                'index'     => 'name',
                'filter_index' => 'name',
            )
        );

        $helper = Mage::helper('M2ePro');
        $this->addColumn(
            'category', array(
                'header'    => Mage::helper('M2ePro')->__('eBay Categories'),
                'align'     => 'left',
                'width'     => '*',
                'type'      => 'options',
                'index'     => 'category',
                'filter'    => 'M2ePro/adminhtml_ebay_grid_column_filter_categoryMode',
                'category_type' => eBayCategory::TYPE_EBAY_MAIN,
                'options'   => array(
                    CategoryModeFilter::MODE_SELECTED     => $helper->__('Primary Category Selected'),
                    CategoryModeFilter::MODE_NOT_SELECTED => $helper->__('Primary Category Not Selected'),
                    CategoryModeFilter::MODE_TITLE        => $helper->__('Primary Category Name/ID')
                ),
                'sortable'                  => false,
                'frame_callback'            => array($this, 'callbackColumnCategories'),
                'filter_condition_callback' => array($this, 'callbackColumnCategoryFilterCallback'),
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
                'field'     => 'listing_product_id',
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'group_order' => $this->getGroupOrder(),
                'actions'   => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('listing_product_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->setGroups(
            array(
                'edit_settings' => Mage::helper('M2ePro')->__('Edit Settings'),
                'other'         => Mage::helper('M2ePro')->__('Other')
            )
        );

        $this->getMassactionBlock()->addItem(
            'editCategories', array(
                'label' => Mage::helper('M2ePro')->__('Edit Categories'),
                'url'   => '',
            ), 'edit_settings'
        );

        $this->getMassactionBlock()->addItem(
            'getSuggestedCategories', array(
                'label' => Mage::helper('M2ePro')->__('Get Suggested Primary Categories'),
                'url'   => '',
            ), 'other'
        );

        $this->getMassactionBlock()->addItem(
            'resetCategories', array(
                'label' => Mage::helper('M2ePro')->__('Reset Categories'),
                'url'   => '',
            ), 'other'
        );

        $this->getMassactionBlock()->addItem(
            'removeItem', array(
                 'label'    => Mage::helper('M2ePro')->__('Remove Item(s)'),
                 'url'      => '',
            ), 'other'
        );

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
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

    //########################################

    public function callbackColumnCategories($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_CategoryInfo $renderer */
        $renderer = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_grid_column_renderer_categoryInfo');
        $renderer->setColumn($column);
        $renderer->setEntityIdField('listing_product_id');
        $renderer->setListing($this->_listing);
        $renderer->setHideUnselectedSpecifics(true);
        $renderer->setCategoriesData($this->getCategoriesData());

        return $renderer->render($row);
    }

    //########################################

    protected function callbackColumnCategoryFilterCallback($collection, $column)
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

        foreach ($this->getCategoriesData() as $listingProductId => $categoryData) {
            if (!isset($categoryData[$categoryType]) ||
                $categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_NONE
            ) {
                $categoryStat['blank'][] = $listingProductId;
                continue;
            }

            $categoryStat['selected'][] = $listingProductId;

            if ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_EBAY) {
                $categoryStat['ebay'][] = $listingProductId;
            }

            if ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE) {
                $categoryStat['attribute'][] = $listingProductId;
            }

            if (!empty($filter['title']) &&
                (strpos($categoryData[$categoryType]['path'], $filter['title']) !== false ||
                 strpos($categoryData[$categoryType]['value'], $filter['title']) !== false)
            ) {
                $categoryStat['path'][] = $listingProductId;
            }
        }

        $ids = array();
        $filter['mode'] == CategoryModeFilter::MODE_NOT_SELECTED && $ids = $categoryStat['blank'];
        $filter['mode'] == CategoryModeFilter::MODE_SELECTED     && $ids = $categoryStat['selected'];
        $filter['mode'] == CategoryModeFilter::MODE_EBAY         && $ids = $categoryStat['ebay'];
        $filter['mode'] == CategoryModeFilter::MODE_ATTRIBUTE    && $ids = $categoryStat['attribute'];
        $filter['mode'] == CategoryModeFilter::MODE_TITLE        && $ids = $categoryStat['path'];

        $collection->addFieldToFilter('listing_product_id', array('in' => $ids));
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/stepTwoModeManuallyGrid',
            array(
                '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function getGroupOrder()
    {
        return array(
            'edit_actions'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'other'            => Mage::helper('M2ePro')->__('Other'),
        );
    }

    protected function getColumnActionsItems()
    {
        return array(
            'getSuggestedCategories' => array(
                'caption' => Mage::helper('catalog')->__('Get Suggested Primary Category'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => "EbayListingCategoryProductGridObj.actions['getSuggestedCategoriesAction']"
            ),
            'editCategories' => array(
                'caption' => Mage::helper('catalog')->__('Edit Categories'),
                'group'   => 'edit_actions',
                'field' => 'id',
                'onclick_action' => "EbayListingCategoryProductGridObj.actions['editCategoriesAction']"
            ),
            'resetCategories' => array(
                'caption' => Mage::helper('catalog')->__('Reset Categories'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => "EbayListingCategoryProductGridObj.actions['resetCategoriesAction']"
            ),
            'removeItem' => array(
                'caption' => Mage::helper('catalog')->__('Remove Item'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => "EbayListingCategoryProductGridObj.actions['removeItemAction']"
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

        $path = 'adminhtml_ebay_category/getChooserEditHtml';
        $urls[$path] = $this->getUrl(
            '*/' . $path,
            array(
                'account_id' => $this->_listing->getAccountId(),
                'marketplace_id' => $this->_listing->getMarketplaceId()
            )
        );
        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $translations = $helper->jsonEncode(
            array(
                'Set eBay Category'   => $helper->__('Set eBay Category'),
                'Category Settings'   => $helper->__('Category Settings'),
                'Specifics'           => $helper->__('Specifics'),

                'Suggested Categories were not assigned.' => $helper->__(
                    'eBay could not assign Categories for %products_count% Products.'
                ),
                'Suggested Categories were assigned.' => $helper->__(
                    'Suggested Categories were received for %products_count% Products.'
                ),
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

    EbayListingCategoryProductGridObj.afterInitPage();
    EbayListingCategoryProductGridObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');

    EbayListingCategoryProductGridObj.validateCategories(
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
    EbayListingCategoryProductGridObj = new EbayListingCategoryProductGrid('{$this->getId()}');
    
    WrapperObj = new AreaWrapper('products_container');
    ProgressBarObj = new ProgressBar('products_progress_bar');
    EbayListingCategoryProductSuggestedSearchObj = new EbayListingCategoryProductSuggestedSearch();
    
</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    protected function getGridIdsJson()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();

        $select->columns('elp.listing_product_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        return implode(',', $connRead->fetchCol($select));
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
                return true;
            }
        }

        return false;
    }

    //########################################
}
