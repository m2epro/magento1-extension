<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_ItemSpecifics_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayConfigurationCategoryViewTabsItemSpecificsEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_configuration_category_view_tabs_itemSpecifics';
        $this->_mode = 'edit';

        $this->_headerText = '';

        $this->_updateButton(
            'reset',
            'onclick',
            'EbayTemplateCategorySpecificsObj.resetSpecifics()'
        );

        $this->_addButton(
            'save_and_continue',
            array(
                'label' => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
                'class' => 'save'
            ),
            1
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category')->load(
            $this->getRequest()->getParam('template_id')
        );

        $isExists = true;
        $template->isCategoryModeEbay() && $isExists = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->exists(
            $template->getCategoryId(),
            $template->getMarketplaceId()
        );

        if (!$isExists) {
            $this->removeButton('reset');
            $this->removeButton('save');
            $this->removeButton('save_and_continue');
        }
    }

    //########################################
}
