<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Specific_Add
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateDescriptionCategorySpecificAdd');
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
        $this->setTemplate('M2ePro/common/amazon/template/description/category/specific/add.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        $additionalTitle = $this->getRequest()->getParam('current_indexed_xpath');
        $additionalTitle = explode('/', ltrim($additionalTitle, '/'));
        array_shift($additionalTitle);
        $additionalTitle = array_map(function($el) { return preg_replace('/-\d+/', '', $el); }, $additionalTitle);
        $this->setData('additional_title', implode(' > ', $additionalTitle));

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        // ---------------------------------------
        $data = array(
            'class'   => 'specifics_done_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm')
        );
        $doneButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $cancelWord = Mage::helper('M2ePro')->__('Cancel');
        $buttonsContainer = <<< HTML
<div style="margin-top: 17px; text-align: right; position: absolute; left: 83.5%; top: 90%;">
    <a href="javascript:void(0)" class="specifics_cancel_button">{$cancelWord}</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    {$doneButton->toHtml()}
</div>
HTML;
        return parent::_toHtml() . $buttonsContainer;
    }

    //########################################
}
