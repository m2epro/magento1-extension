<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Specific_Add
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateDescriptionCategorySpecificAdd');
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
        $this->setTemplate('M2ePro/walmart/template/category/categories/specific/add.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        $pathParts = explode('/', ltrim($this->getRequest()->getParam('current_indexed_xpath'), '/'));
        array_walk(
            $pathParts, function (&$el) {
            $el = preg_replace('/-\d+/', '', $el);
            $el = preg_replace('/(?<!^)[A-Z0-9]/', ' $0', $el);
            $el = ucfirst($el);
            }
        );

        $additionalTitle = implode(' > ', $pathParts);
        $this->setData('additional_title', Mage::helper('M2ePro')->escapeHtml($additionalTitle));

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
