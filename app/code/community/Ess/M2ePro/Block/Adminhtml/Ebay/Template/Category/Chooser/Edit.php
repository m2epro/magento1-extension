<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Edit
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_categoryType;
    protected $_selectedCategory = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateCategoryChooserEdit');
        $this->setTemplate('M2ePro/ebay/template/category/chooser/edit.phtml');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    protected function _toHtml()
    {
        $tabsContainer = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_category_chooser_tabs',
            '',
            array('category_type' => $this->getCategoryType())
        );
        $tabsContainer->setDestElementId('chooser_tabs_container');

        $data = array(
            'id'      => 'category_edit_confirm_button',
            'class'   => '',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayTemplateCategoryChooserObj.confirmCategory();',
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');

        $buttonsContainer = <<< HTML

<div id="chooser_buttons_container">
    <a href="javascript:void(0)" onclick="EbayTemplateCategoryChooserObj.cancelPopUp()">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
    <script type="text/javascript">ebayTemplateCategoryChooserTabsJsTabs.moveTabContentInDest();</script>
</div>

HTML;

        return parent::_toHtml() .
               $tabsContainer->toHtml() .
               '<div id="chooser_tabs_container"></div>' .
               $buttonsContainer;
    }

    //########################################

    public function getCategoryType()
    {
        if ($this->_categoryType === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Category type is not set.');
        }

        return $this->_categoryType;
    }

    public function setCategoryType($categoryType)
    {
        $this->_categoryType = $categoryType;
        return $this;
    }

    public function getCategoryTitle()
    {
        $titles = Mage::helper('M2ePro/Component_Ebay_Category')->getCategoryTitles();

        return isset($titles[$this->_categoryType]) ? $titles[$this->_categoryType] : '';
    }

    public function getSelectedCategory()
    {
        return $this->_selectedCategory;
    }

    public function setSelectedCategory(array $selectedCategory)
    {
        $this->_selectedCategory = $selectedCategory;
        return $this;
    }

    public function getSelectedCategoryPathHtml()
    {
        $helper = Mage::helper('M2ePro');
        if (!isset($this->_selectedCategory['mode']) ||
            $this->_selectedCategory['mode'] == TemplateCategory::CATEGORY_MODE_NONE
        ) {
            return <<<HTML
<span style="font-style: italic; color: grey">{$helper->__('Not Selected')}</span>
HTML;
        }

        return $this->_selectedCategory['mode'] == TemplateCategory::CATEGORY_MODE_EBAY
            ? "{$this->_selectedCategory['path']} ({$this->_selectedCategory['value']})"
            : $this->_selectedCategory['path'];
    }

    //########################################
}
