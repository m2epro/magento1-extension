<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Product_Grid
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
        $this->setId('ebayListingCategoryProductGrid');
        // ---------------------------------------

        $this->listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection
        // ---------------------------------------
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name');
        // ---------------------------------------

        // ---------------------------------------
        $collection->getSelect()->distinct();
        // ---------------------------------------

        // Set filter store
        // ---------------------------------------
        $store = Mage::app()->getStore((int)$this->listing->getData('store_id'));

        if ($store->getId()) {
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                NULL,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'thumbnail',
                'catalog_product/thumbnail',
                'entity_id',
                NULL,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('thumbnail');
        }
        // ---------------------------------------

        // ---------------------------------------
        $productAddIds = (array)json_decode($this->listing->getData('product_add_ids'), true);

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id'
            ),
            '{{table}}.listing_id='.(int)$this->listing->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id'
            )
        );

        $collection->getSelect()->where('lp.id IN (?)', $productAddIds);
        // ---------------------------------------

        // Set collection to grid
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
            'header'    => Mage::helper('M2ePro')->__('Product Title'),
            'align'     => 'left',
            'width'     => '350px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $this->addColumn('category', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Categories'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'options',
            'index'     => 'category',
            'filter_index' => 'entity_id',
            'options'   => array(
                1 => Mage::helper('M2ePro')->__('Primary eBay Category Selected'),
                0 => Mage::helper('M2ePro')->__('Primary eBay Category Not Selected')
            ),
            'frame_callback' => array($this, 'callbackColumnCategoryCallback'),
            'filter_condition_callback' => array($this, 'callbackColumnCategoryFilterCallback')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'center',
            'width'     => '100px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'field'     => 'listing_product_id',
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'group_order' => $this->getGroupOrder(),
            'actions'   => $this->getColumnActionsItems()
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('listing_product_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // ---------------------------------------

        $this->getMassactionBlock()->setGroups(array(
            'edit_settings'         => Mage::helper('M2ePro')->__('Edit Settings'),
            'other'                 => Mage::helper('M2ePro')->__('Other')
        ));

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('editCategories', array(
            'label' => Mage::helper('M2ePro')->__('All Categories'),
            'url'   => '',
        ), 'edit_settings');

        $this->getMassactionBlock()->addItem('editPrimaryCategories', array(
            'label' => Mage::helper('M2ePro')->__('Primary Categories'),
            'url'   => '',
        ), 'edit_settings');

        if ($this->listing->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $this->getMassactionBlock()->addItem('editStorePrimaryCategories', array(
                'label' => Mage::helper('M2ePro')->__('Store Primary Categories'),
                'url'   => '',
            ), 'edit_settings');
        }

        $this->getMassactionBlock()->addItem('getSuggestedCategories', array(
            'label' => Mage::helper('M2ePro')->__('Get Suggested Primary Categories'),
            'url'   => '',
        ), 'other');

        $this->getMassactionBlock()->addItem('resetCategories', array(
            'label' => Mage::helper('M2ePro')->__('Reset Categories'),
            'url'   => '',
        ), 'other');

        $this->getMassactionBlock()->addItem('removeItem', array(
             'label'    => Mage::helper('M2ePro')->__('Remove Item(s)'),
             'url'      => '',
        ), 'other');
        // ---------------------------------------

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
                    NULL,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
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

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        return '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';
    }

    public function callbackColumnCategoryCallback($value, $row, $column, $isExport)
    {
        $productId   = $row->getData('listing_product_id');
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue('ebay_listing_category_settings/mode_product');

        $html = '';

        if ($sessionData[$productId]['category_main_mode']) {
            $categoryType = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN;
            $categoryMode = $sessionData[$productId]['category_main_mode'];
            $categoryAttribute = $sessionData[$productId]['category_main_attribute'];
            $categoryId = $sessionData[$productId]['category_main_id'];
            $categoryPath = $sessionData[$productId]['category_main_path'];

            $html .= $this->renderCategory(
                $categoryType,
                $categoryMode,
                $categoryAttribute,
                $categoryId,
                $categoryPath
            );
        }

        if ($sessionData[$productId]['category_secondary_mode']) {
            $categoryType = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY;
            $categoryMode = $sessionData[$productId]['category_secondary_mode'];
            $categoryAttribute = $sessionData[$productId]['category_secondary_attribute'];
            $categoryId = $sessionData[$productId]['category_secondary_id'];
            $categoryPath = $sessionData[$productId]['category_secondary_path'];

            if ($html != '') {
                $html .= '<br/>';
            }

            $html .= $this->renderCategory(
                $categoryType,
                $categoryMode,
                $categoryAttribute,
                $categoryId,
                $categoryPath
            );
        }

        if ($sessionData[$productId]['store_category_main_mode']) {
            $categoryType = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN;
            $categoryMode = $sessionData[$productId]['store_category_main_mode'];
            $categoryAttribute = $sessionData[$productId]['store_category_main_attribute'];
            $categoryId = $sessionData[$productId]['store_category_main_id'];
            $categoryPath = $sessionData[$productId]['store_category_main_path'];

            if ($html != '') {
                $html .= '<br/>';
            }

            $html .= $this->renderStoreCategory(
                $categoryType,
                $categoryMode,
                $categoryAttribute,
                $categoryId,
                $categoryPath
            );
        }

        if ($sessionData[$productId]['store_category_secondary_mode']) {
            $categoryType = Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY;
            $categoryMode = $sessionData[$productId]['store_category_secondary_mode'];
            $categoryAttribute = $sessionData[$productId]['store_category_secondary_attribute'];
            $categoryId = $sessionData[$productId]['store_category_secondary_id'];
            $categoryPath = $sessionData[$productId]['store_category_secondary_path'];

            if ($html != '') {
                $html .= '<br/>';
            }

            $html .= $this->renderStoreCategory(
                $categoryType,
                $categoryMode,
                $categoryAttribute,
                $categoryId,
                $categoryPath
            );
        }

        if ($html == '') {
            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $label = Mage::helper('M2ePro')->__('Not Selected');

            $html .= <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="color: gray; font-style: italic;">{$label}</span>
HTML;
        }

        return $html;
    }

    private function getCategoryTypeName($categoryType)
    {
        $name = '';

        switch ($categoryType) {
            case Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN:
                $name = Mage::helper('M2ePro')->__('Primary eBay Category');
                break;
            case Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY:
                $name = Mage::helper('M2ePro')->__('Secondary eBay Category');
                break;
            case Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN:
                $name = Mage::helper('M2ePro')->__('Primary eBay Store Category');
                break;
            case Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY:
                $name = Mage::helper('M2ePro')->__('Secondary eBay Store Category');
                break;
        }

        return '<span style="text-decoration: underline;">'.$name.'</span>';
    }

    private function renderCategory($categoryType, $mode, $attribute, $id, $path)
    {
        $info = '';

        switch ($mode) {
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                $info = $this->getCategoryPathLabel($path, $id);
                break;
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                $info = $this->getCategoryAttributeLabel($attribute);
                break;
        }

        if (!$info) {
            return '';
        }

        $categoryTypeName = $this->getCategoryTypeName($categoryType);

        return <<<HTML
{$categoryTypeName}<br/>
{$info}
HTML;
    }

    private function renderStoreCategory($categoryType, $mode, $attribute, $id, $path)
    {
        $info = '';

        switch ($mode) {
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                $info = $this->getCategoryPathLabel($path, $id);
                break;
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                $info = $this->getCategoryAttributeLabel($attribute);
                break;
        }

        if (!$info) {
            return '';
        }

        $categoryTypeName = $this->getCategoryTypeName($categoryType);

        return <<<HTML
{$categoryTypeName}<br/>
{$info}
HTML;
    }

    private function getCategoryAttributeLabel($attributeCode)
    {
        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
            $attributeCode,
            $this->listing->getData('store_id')
        );

        $result = Mage::helper('M2ePro')->__('Magento Attribute') . '&nbsp;->&nbsp;';
        $result .= Mage::helper('M2ePro')->escapeHtml($attributeLabel);

        return '<span style="padding-left: 10px; display: inline-block;">' . $result . '</span>';
    }

    private function getCategoryPathLabel($categoryPath, $categoryId = NULL)
    {
        $result = $categoryPath;

        if ($categoryId) {
            $result .= '&nbsp;(' . $categoryId . ')';
        }

        return '<div style="padding-left: 10px; display: inline-block;">' . $result . '</div>';
    }

    //########################################

    protected function callbackColumnCategoryFilterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $sessionKey = 'ebay_listing_category_settings';
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($sessionKey);

        $primaryCategory = array('selected' => array(), 'blank' => array());
        foreach ($sessionData['mode_product'] as $listingProductId => $listingProductData) {
            if ($listingProductData['category_main_mode'] !=
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                $primaryCategory['selected'][] = $listingProductId;
                continue;
            }
            $primaryCategory['blank'][] = $listingProductId;
        }

        if ($value == 0) {
            $collection->addFieldToFilter('listing_product_id', array('in' => $primaryCategory['blank']));
        } else {
            $collection->addFieldToFilter('listing_product_id', array('in' => $primaryCategory['selected']));
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/stepTwoModeProductGrid',
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
        $actions = array(
            'getSuggestedCategories' => array(
                'caption' => Mage::helper('catalog')->__('Get Suggested Primary Category'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                                    .'actions[\'getSuggestedCategoriesAction\']'
            ),
            'editCategories' => array(
                'caption' => Mage::helper('catalog')->__('All Categories'),
                'group'   => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                    .'actions[\'editCategoriesAction\']'
            ),
            'editPrimaryCategories' => array(
                'caption' => Mage::helper('catalog')->__('Primary Category'),
                'group'   => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                                    .'actions[\'editPrimaryCategoriesAction\']'
            )
        );

        if ($this->listing->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $actions['editStorePrimaryCategories'] = array(
                'caption' => Mage::helper('catalog')->__('Store Primary Category'),
                'group'   => 'edit_actions',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                                    .'actions[\'editStorePrimaryCategoriesAction\']'
            );
        }

        $actions = array_merge($actions, array(
            'resetCategories' => array(
                'caption' => Mage::helper('catalog')->__('Reset Categories'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                                    .'actions[\'resetCategoriesAction\']'
            ),
            'removeItem' => array(
                'caption' => Mage::helper('catalog')->__('Remove Item'),
                'group'   => 'other',
                'field' => 'id',
                'onclick_action' => 'EbayListingCategoryProductGridHandlerObj.'
                                    .'actions[\'removeItemAction\']'
            ),
        ));

        return $actions;
    }

    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------
        $urls = Mage::helper('M2ePro')
            ->getControllerActions(
                'adminhtml_ebay_listing_categorySettings',
                array(
                    '_current' => true
                )
            );

        $path = 'adminhtml_ebay_listing_categorySettings';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'step' => 3,
            '_current' => true
        ));

        $path = 'adminhtml_ebay_category/getChooserEditHtml';
        $urls[$path] = $this->getUrl(
            '*/' . $path,
            array(
                'account_id' => $this->listing->getAccountId(),
                'marketplace_id' => $this->listing->getMarketplaceId()
            )
        );

        $urls = json_encode($urls);
        // ---------------------------------------

        // ---------------------------------------
        $translations = array();
        // M2ePro_TRANSLATIONS
        // You have not selected the Primary eBay Category for some Products.
        $text = 'You have not selected the Primary eBay Category for some Products.';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Are you sure?
        $text = 'Are you sure?';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // eBay could not assign Categories for %product_tite% Products.
        $text = 'eBay could not assign Categories for %product_title% Products.';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Suggested Categories were successfully Received for %product_title% Product(s).
        $text = 'Suggested Categories were successfully Received for %product_title% Product(s).';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Set eBay Category
        $text = 'Set eBay Category';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Set eBay Category for Product(s)
        $text = 'Set eBay Category for Product(s)';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Set eBay Primary Category for Product(s)
        $text = 'Set eBay Primary Category for Product(s)';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $translations = json_encode($translations);
        // ---------------------------------------

        // ---------------------------------------
        $constants = Mage::helper('M2ePro')->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        // ---------------------------------------

        $getSuggested = json_encode((bool)Mage::helper('M2ePro/Data_Global')->getValue('get_suggested'));

        $commonJs = <<<HTML
<script type="text/javascript">
    EbayListingCategoryProductGridHandlerObj.afterInitPage();
    EbayListingCategoryProductGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');
</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants},'Ess_M2ePro_Helper_Component_Ebay_Category');

    WrapperObj = new AreaWrapper('products_container');
    ProgressBarObj = new ProgressBar('products_progress_bar');

    EbayListingCategoryProductGridHandlerObj = new EbayListingCategoryProductGridHandler('{$this->getId()}');
    EbayListingCategoryProductSuggestedSearchHandlerObj = new EbayListingCategoryProductSuggestedSearchHandler();

    if ({$getSuggested}) {
        Event.observe(window, 'load', function() {
            EbayListingCategoryProductGridHandlerObj.getSuggestedCategoriesForAll();
        });
    }
</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    private function getGridIdsJson()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();

        $select->columns('elp.listing_product_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        return implode(',',$connRead->fetchCol($select));
    }

    //########################################
}