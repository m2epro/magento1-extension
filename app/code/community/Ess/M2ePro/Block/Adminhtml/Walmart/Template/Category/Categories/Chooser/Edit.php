<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Edit
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    protected $_marketplaceId;
    protected $_selectedCategory = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateCategoryCategoriesChooserEdit');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->setTemplate('M2ePro/walmart/template/category/categories/chooser/edit.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'      => 'category_edit_confirm_button',
            'class'   => '',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'WalmartTemplateCategoryCategoriesChooserHandlerObj.confirmCategory();',
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        $buttonsContainer = <<< HTML
<div id="chooser_buttons_container">
    <a href="javascript:void(0)"
       onclick="WalmartTemplateCategoryCategoriesChooserHandlerObj.cancelPopUp()">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
    <script type="text/javascript">walmartTemplateCategoryCategoriesChooserTabsJsTabs.moveTabContentInDest();</script>
</div>
HTML;

        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Tabs $tabsBlock */
        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_chooser_tabs';
        $tabsBlock = $this->getLayout()->createBlock($blockName);

        return parent::_toHtml() .
               $tabsBlock->toHtml() .
               '<div id="chooser_tabs_container"></div>' .
               $buttonsContainer;
    }

    //########################################

    public function getSelectedCategory()
    {
        return $this->_selectedCategory;
    }

    public function setSelectedCategory(array $selectedCategory)
    {
        $this->_selectedCategory = $selectedCategory;
        return $this;
    }

    // ---------------------------------------

    public function setMarketplaceId($value)
    {
        $this->_marketplaceId = $value;
        return $this;
    }

    //########################################
}
