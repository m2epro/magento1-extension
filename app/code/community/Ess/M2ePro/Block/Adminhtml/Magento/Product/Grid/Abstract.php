<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public $hideMassactionColumn = false;
    protected $hideMassactionDropDown = false;

    protected $showAdvancedFilterProductsOption = true;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->isAjax = json_encode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    public function setCollection($collection)
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = 0;
        if ($listing) {
            $storeId = $listing['store_id'];
        }

        $collection->setStoreId($storeId);

        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::helper('M2ePro/Data_Global')->getValue('rule_model');
        $ruleModel->setAttributesFilterToCollection($collection);

        parent::setCollection($collection);
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set fake action
        // ---------------------------------------
        if ($this->getMassactionBlock()->getCount() == 0) {
            $this->getMassactionBlock()->addItem('fake', array(
                'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
                'url'   => '#',
            ));
        }
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionColumn()
    {
        if ($this->hideMassactionColumn) {
            return;
        }
        parent::_prepareMassactionColumn();
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    public function getMassactionBlockHtml()
    {
        $advancedFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_product_rule');
        $advancedFilterBlock->setShowHideProductsOption($this->showAdvancedFilterProductsOption);
        $advancedFilterBlock->setGridJsObjectName($this->getJsObjectName());

        return $advancedFilterBlock->toHtml() . (($this->hideMassactionColumn)
            ? '' :  parent::getMassactionBlockHtml());
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $productId = (int)$value;

        $storeId = 0;
        if ($listing) {
            $storeId = (int)$listing['store_id'];
        }

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
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }
        return $value;
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!$this->getChild('advanced_filter_button')) {
            $data = array(
                'label'   => Mage::helper('adminhtml')->__('Show Advanced Filter'),
                'onclick' => 'ProductGridHandlerObj.advancedFilterToggle()',
                'class'   => 'task',
                'id'      => 'advanced_filter_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');
            $buttonBlock->setData($data);
            $this->setChild('advanced_filter_button', $buttonBlock);
        }

        return $this->getChildHtml('advanced_filter_button');
    }

    public function getMainButtonsHtml()
    {
        $html = '';

        if ($this->getFilterVisibility()) {
            $html .= $this->getResetFilterButtonHtml();
            if (!$this->isShowRuleBlock()) {
                $html .= $this->getAdvancedFilterButtonHtml();
            }
            $html .= $this->getSearchButtonHtml();
        }

        return $html;
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $css = '';

        if ($this->hideMassactionDropDown) {
            $css = <<<HTML
<style type="text/css">
    table.massaction div.right {
        display: none;
    }
</style>
HTML;
        }
        // ---------------------------------------

        // ---------------------------------------
        $isShowRuleBlock = json_encode($this->isShowRuleBlock());

        $commonJs = <<<HTML
<script type="text/javascript">
    var init = function() {
        if ({$isShowRuleBlock}) {
            $('listing_product_rules').show();
            if ($('advanced_filter_button')) {
                $('advanced_filter_button').simulate('click');
            }
        }
    };

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);
</script>
HTML;
        // ---------------------------------------

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $commonJs . parent::_toHtml();
        }

        // ---------------------------------------
        $helper = Mage::helper('M2ePro');

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $createEmptyListingMessage = $helper->escapeJs($helper->__('Are you sure you want to create empty Listing?'));

        $showAdvancedFilterButtonText = $helper->escapeJs($helper->__('Show Advanced Filter'));
        $hideAdvancedFilterButtonText = $helper->escapeJs($helper->__('Hide Advanced Filter'));

        $js = <<<HTML
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.create_empty_listing_message = '{$createEmptyListingMessage}';
    M2ePro.text.show_advanced_filter = '{$showAdvancedFilterButtonText}';
    M2ePro.text.hide_advanced_filter = '{$hideAdvancedFilterButtonText}';

    ProductGridHandlerObj = new ListingProductGridHandler();
    ProductGridHandlerObj.setGridId('{$this->getJsObjectName()}');

    var init = function () {
        {$this->getJsObjectName()}.doFilter = ProductGridHandlerObj.setFilter;
        {$this->getJsObjectName()}.resetFilter = ProductGridHandlerObj.resetFilter;
    };

    {$this->isAjax} ? init()
                    : Event.observe(window, 'load', init);
</script>
HTML;
        // ---------------------------------------

        return $css . parent::_toHtml() . $js . $commonJs;
    }

    //########################################

    protected function isShowRuleBlock()
    {
        $ruleData = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('rule_prefix')
        );

        $showHideProductsOption = Mage::helper('M2ePro/Data_Session')->getValue(
            Mage::helper('M2ePro/Data_Global')->getValue('hide_products_others_listings_prefix')
        );

        is_null($showHideProductsOption) && $showHideProductsOption = 1;
        return !empty($ruleData) || ($this->showAdvancedFilterProductsOption && $showHideProductsOption);
    }

    //########################################
}