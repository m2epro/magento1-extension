<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Edit
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
        $this->setId('amazonTemplateDescriptionCategoryChooserEdit');
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
        $this->setTemplate('M2ePro/common/amazon/template/description/category/chooser/edit.phtml');
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
            'onclick' => 'AmazonTemplateDescriptionCategoryChooserHandlerObj.confirmCategory();',
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        $buttonsContainer = <<< HTML
<div id="chooser_buttons_container">
    <a href="javascript:void(0)"
       onclick="AmazonTemplateDescriptionCategoryChooserHandlerObj.cancelPopUp()">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
    <script type="text/javascript">amazonTemplateDescriptionCategoryChooserTabsJsTabs.moveTabContentInDest();</script>
</div>
HTML;

        /** @var Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Tabs $tabsBlock */
        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_chooser_tabs';
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
