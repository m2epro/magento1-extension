<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateCategoryEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/template/category/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $templateModel = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // ---------------------------------------
        $marketplaces = Mage::helper('M2ePro/Component_Walmart')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->setData('marketplaces', $marketplaces['items']);
        // ---------------------------------------

        // ---------------------------------------
        $marketplaceLocked = $categoryLocked = false;

        if ($templateModel && $templateModel->getId()) {
            $marketplaceLocked = $templateModel->isLocked();
            $categoryLocked = $templateModel->isLockedForCategoryChange();
        }

        $this->setData('marketplace_locked', $marketplaceLocked);
        $this->setData('category_locked', $categoryLocked);
        // ---------------------------------------

        // ---------------------------------------
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $this->setData('all_attributes', $attributeHelper->getAll());
        $this->setData('general_attributes', $attributeHelper->getGeneralFromAllAttributeSets());

        return parent::_beforeToHtml();
    }

    //########################################
}
