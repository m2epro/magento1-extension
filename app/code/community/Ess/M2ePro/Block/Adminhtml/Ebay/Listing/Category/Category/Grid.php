<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Category_Grid extends Ess_M2ePro_Block_Adminhtml_Category_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );

        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->addAttributeToSelect('name');

        $collection->addFieldToFilter(array(
            array('attribute' => 'entity_id', 'in' => array_keys($this->getCategoriesData()))
        ));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ####################################

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

        $this->addColumn('ebay_categories', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Categories'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('M2ePro')->__('Primary eBay Category Selected'),
                0 => Mage::helper('M2ePro')->__('Primary eBay Category Not Selected')
            ),
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnEbayCategories'),
            'filter_condition_callback' => array($this, 'callbackFilterEbayCategories')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'center',
            'width'     => '100px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'actions'   => $this->getColumnActionsItems()
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('editCategories', array(
            'label'    => Mage::helper('M2ePro')->__('Edit All Categories'),
        ));

        $this->getMassactionBlock()->addItem('editPrimaryCategories', array(
            'label' => Mage::helper('M2ePro')->__('Edit Primary Categories'),
            'url'   => '',
        ));

        if ($this->listing->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $this->getMassactionBlock()->addItem('editStorePrimaryCategories', array(
                'label' => Mage::helper('M2ePro')->__('Edit Store Primary Categories'),
                'url'   => '',
            ));
        }
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    public function callbackColumnEbayCategories($value, $row, $column, $isExport)
    {
        $categoriesData = $this->getCategoriesData();

        $html = '';

        $html .= $this->renderEbayCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Primary Category'),
            $categoriesData[$row->getId()],
            'category_main'
        );

        $html .= $this->renderEbayCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Secondary Category'),
            $categoriesData[$row->getId()],
            'category_secondary'
        );
        $html .= $this->renderStoreCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Store Primary Category'),
            $categoriesData[$row->getId()],
            'store_category_main'
        );

        $html .= $this->renderStoreCategoryInfo(
            Mage::helper('M2ePro')->__('eBay Store Secondary Category'),
            $categoriesData[$row->getId()],
            'store_category_secondary'
        );

        if (empty($html)) {

            $helper = Mage::helper('M2ePro');

            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $html .= <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="font-style: italic; color: gray">{$helper->__('Not Selected')}</span>
HTML;

        }

        return $html;
    }

    // ####################################

    protected function callbackFilterEbayCategories($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $primaryCategory = array('selected' => array(), 'blank' => array());

        foreach ($this->getCategoriesData() as $categoryId => $templateData) {
            if ($templateData['category_main_mode'] != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                $primaryCategory['selected'][] = $categoryId;
                continue;
            }

            $primaryCategory['blank'][] = $categoryId;
        }

        if ($value == 0) {
            $collection->addFieldToFilter('entity_id', array('in' => $primaryCategory['blank']));
        } else {
            $collection->addFieldToFilter('entity_id', array('in' => $primaryCategory['selected']));
        }
    }

    // ####################################

    protected function renderEbayCategoryInfo($title, $data, $key)
    {
        $info = '';

        if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $info = $data[$key.'_path'];
            $info.= '&nbsp;('.$data[$key.'_id'].')';
        } elseif ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $info = Mage::helper('M2ePro')->__(
                'Magento Attribute > %attribute_label%',
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'],
                    $this->listing->getStoreId()
                )
            );
        }

        return $this->renderCategoryInfo($title,$info);
    }

    protected function renderStoreCategoryInfo($title, $data, $key)
    {
        $info = '';

        if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $info = $data[$key.'_path'];
            $info.= '&nbsp;('.$data[$key.'_id'].')';
        } elseif ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $info = Mage::helper('M2ePro')->__(
                'Magento Attribute > %attribute_label%',
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'],
                    $this->listing->getStoreId()
                )
            );
        }

        return $this->renderCategoryInfo($title,$info);
    }

    protected function renderCategoryInfo($title, $info)
    {
        if (!$info) {
            return '';
        }

        return <<<HTML
<div>
    <span style="text-decoration: underline">{$title}:</span>
    <p style="padding: 2px 0 0 10px;">
        {$info}
    </p>
</div>
HTML;

    }

    // ####################################

    protected function getColumnActionsItems()
    {
        $actions = array(
            'editCategories' => array(
                'caption' => Mage::helper('catalog')->__('Edit All Categories'),
                'field'   => 'id',
                'onclick_action' => 'EbayListingCategoryCategoryGridHandlerObj.'
                                    .'actions[\'editCategoriesAction\']'
            ),

            'editPrimaryCategories' => array(
                'caption' => Mage::helper('catalog')->__('Edit Primary Category'),
                'field'   => 'id',
                'onclick_action' => 'EbayListingCategoryCategoryGridHandlerObj.'
                                    .'actions[\'editPrimaryCategoriesAction\']'
            )
        );

        if ($this->listing->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $actions['editStorePrimaryCategories'] = array(
                'caption' => Mage::helper('catalog')->__('Edit Store Primary Category'),
                'field'   => 'id',
                'onclick_action' => 'EbayListingCategoryCategoryGridHandlerObj.'
                                    .'actions[\'editStorePrimaryCategoriesAction\']'
            );
        }

        return $actions;
    }

    // ####################################

    protected function _toHtml()
    {
        //------------------------------
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

        $urls = json_encode($urls);
        //------------------------------

        //------------------------------
        $translations = array();

        // M2ePro_TRANSLATIONS
        // Done
        $text = 'Done';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Cancel
        $text = 'Cancel';
        $translations[$text] = Mage::helper('M2ePro')->__($text);
        // M2ePro_TRANSLATIONS
        // Set eBay Categories
        $text = 'Set eBay Categories';
        $translations[$text] = Mage::helper('M2ePro')->__($text);

        $translations = json_encode($translations);
        //------------------------------

        //------------------------------
        $constants = Mage::helper('M2ePro')->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        //------------------------------

        $commonJs = <<<HTML
<script type="text/javascript">
    EbayListingCategoryCategoryGridHandlerObj.afterInitPage();
</script>
HTML;

        $disableContinue = '';
        if ($this->getCollection()->getSize() === 0) {
            $disableContinue = <<<JS
$('ebay_listing_category_continue_btn').addClassName('disabled').onclick = function() {
    return null;
};
JS;
        }

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

    {$disableContinue}

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants},'Ess_M2ePro_Helper_Component_Ebay_Category');

    EbayListingCategoryCategoryGridHandlerObj = new EbayListingCategoryCategoryGridHandler('{$this->getId()}');

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    // ####################################
}