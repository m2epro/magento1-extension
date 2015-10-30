<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_SellingFormat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateSellingFormatEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/template/selling_format/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('currencies', Mage::helper('M2ePro/Component_Amazon')->getCurrencies());
        // ---------------------------------------

        // ---------------------------------------
        $this->customerGroups = Mage::getModel('customer/group')->getCollection()->toOptionArray();
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}