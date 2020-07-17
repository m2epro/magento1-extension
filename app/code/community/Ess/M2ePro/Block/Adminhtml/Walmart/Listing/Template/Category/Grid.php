<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Template_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_productsIds        = array();
    protected $_magentoCategoryIds = array();
    protected $_marketplaceId;

    protected $_mapToTemplateJsFn     = 'ListingGridObj.templateCategory.mapToTemplateCategory';
    protected $_createNewTemplateJsFn = 'ListingGridObj.templateCategory.createTemplateCategoryInNewTab';

    //########################################

    /**
     * @return string
     */
    public function getMapToTemplateJsFn()
    {
        return $this->_mapToTemplateJsFn;
    }

    /**
     * @param string $mapToTemplateLink
     */
    public function setMapToTemplateJsFn($mapToTemplateLink)
    {
        $this->_mapToTemplateJsFn = $mapToTemplateLink;
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getCreateNewTemplateJsFn()
    {
        return $this->_createNewTemplateJsFn;
    }

    /**
     * @param string $createNewTemplateJsFn
     */
    public function setCreateNewTemplateJsFn($createNewTemplateJsFn)
    {
        $this->_createNewTemplateJsFn = $createNewTemplateJsFn;
    }

    // ---------------------------------------

    /**
     * @param mixed $productsIds
     */
    public function setProductsIds($productsIds)
    {
        $this->_productsIds = $productsIds;
    }

    /**
     * @return mixed
     */
    public function getProductsIds()
    {
        return $this->_productsIds;
    }

    // ---------------------------------------

    public function setMagentoCategoryIds($magentoCategoryIds)
    {
        $this->_magentoCategoryIds = $magentoCategoryIds;
    }

    public function getMagentoCategoryIds()
    {
        return $this->_magentoCategoryIds;
    }

    // ---------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartTemplateCategoryGrid');

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(true);
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    // ---------------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Resource_Walmart_Template_Category_Collection $collection */
        $collection = Mage::getModel('M2ePro/Walmart_Template_Category')->getCollection();
        $collection->addFieldToFilter('marketplace_id', $this->getMarketplaceId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ---------------------------------------

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter_index' => 'title',
            'sortable'     => true,
            'filter_condition_callback' => array($this, 'callbackFilterTitle'),
            'frame_callback' => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'action', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'left',
            'type'         => 'number',
            'width'        => '55px',
            'index'        => 'id',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnAction')
            )
        );
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                    'id'        => 'category_template_refresh_btn',
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => $this->getJsObjectName().'.reload()'
                    )
                )
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    public function getMainButtonsHtml()
    {
        return $this->getRefreshButtonHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $templateCategoryEditUrl = $this->getUrl(
            '*/adminhtml_walmart_template_category/edit', array(
            'id' => $row->getData('id')
            )
        );

        $title = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
                                                      : Mage::helper('M2ePro')->__('N/A');

        return <<<HTML
<a target="_blank" href="{$templateCategoryEditUrl}">{$title}</a>
<div>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;

    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');

        return <<<HTML
<a href="javascript:void(0);" onclick="{$this->getMapToTemplateJsFn()}(this, {$value});">{$assignText}</a>
HTML;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Template_Category_Collection $collection */

        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'title LIKE ? OR category_path LIKE ? OR browsenode_id LIKE ?', '%'.$value.'%'
        );
    }

    //########################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());
        $magentoIdsStr  = implode(',', $this->getMagentoCategoryIds());

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#walmartTemplateCategoryGrid div.grid th').each(function(el) {
        el.style.padding = '5px 5px';
    });

    $$('#walmartTemplateCategoryGrid div.grid td').each(function(el) {
        el.style.padding = '5px 5px';
    });

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';
    {$this->getJsObjectName()}.reloadParams['magento_categories_ids'] = '{$magentoIdsStr}';
    {$this->getJsObjectName()}.reloadParams['create_new_template_js_function'] = '{$this->getCreateNewTemplateJsFn()}';

</script>
HTML;

        // ---------------------------------------
        $templateCategoryNewUrl = $this->getNewTemplateCategoryUrl();

        $data = array(
            'id'    => 'add_new_category_policy',
            'label' => Mage::helper('M2ePro')->__('Add New Category Policy'),
            'style' => 'float: right;',
            'onclick' => "{$this->getCreateNewTemplateJsFn()}('$templateCategoryNewUrl')"
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $buttonBlockHtml = ($this->canDisplayContainer()) ? $buttonBlock->toHtml(): '';

        return parent::_toHtml() . $buttonBlockHtml . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/viewTemplateCategoriesGrid', array(
            '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function getMarketplaceId()
    {
        if (empty($this->_marketplaceId)) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $productsIds          = $this->getProductsIds();
            $listingProduct       = Mage::helper('M2ePro/Component_Walmart')->getObject(
                'Listing_Product', $productsIds[0]
            );
            $this->_marketplaceId = $listingProduct->getListing()->getMarketplaceId();
        }

        return $this->_marketplaceId;
    }

    // ---------------------------------------

    protected function setNoTemplatesText()
    {
        $templateCategoryEditUrl = $this->getNewTemplateCategoryUrl();

        $messageTxt = Mage::helper('M2ePro')->__('Category Policies are not found for current Marketplace.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Category Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    id="templateCategory_addNew_link"
    onclick="{$this->getCreateNewTemplateJsFn()}('{$templateCategoryEditUrl}');">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateCategoryUrl()
    {
        return $this->getUrl(
            '*/adminhtml_walmart_template_category/new', array(
                'marketplace_id' => $this->getMarketplaceId(),
                'close_on_save' => 1
            )
        );
    }

    //########################################
}
