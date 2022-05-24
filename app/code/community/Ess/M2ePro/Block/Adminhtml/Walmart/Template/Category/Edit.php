<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Edit
    extends Ess_M2ePro_Block_Adminhtml_Walmart_Template_Edit
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartTemplateCategoryEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_template_category';
        $this->_mode = 'edit';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();

            if ($this->isEditMode()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit %component_name% Category Policy "%template_title%"',
                    $componentName,
                    $this->escapeHtml(Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Add %component_name% Category Policy',
                    $componentName
                );
            }
        } else {
            if ($this->isEditMode()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit Category Policy "%template_title%"',
                    $this->escapeHtml(Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__("Add Category Policy");
            }
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = Mage::helper('M2ePro')->getBackUrl('index');
        $this->_addButton(
            'back',
            array(
                'id'      => 'back_button',
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'WalmartTemplateCategoryObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );

        $isSaveAndClose = (bool)$this->getRequest()->getParam('close_on_save', false);

        if (!$isSaveAndClose && $this->isEditMode()) {
            $headId = 'walmart-template-category';
            $this->_addButton(
                'duplicate',
                array(
                    'id'      => 'duplicate_button',
                    'label'   => Mage::helper('M2ePro')->__('Duplicate'),
                    'onclick' => "WalmartTemplateCategoryObj.duplicate_click('{$headId}')",
                    'class'   => 'add M2ePro_duplicate_button'
                )
            );

            $this->_addButton(
                'delete',
                array(
                    'id'      => 'delete_button',
                    'label'   => Mage::helper('M2ePro')->__('Delete'),
                    'onclick' => 'WalmartTemplateCategoryObj.delete_click()',
                    'class'   => 'delete M2ePro_delete_button'
                )
            );
        }

        if ($isSaveAndClose) {
            $this->removeButton('back');

            $this->_addButton(
                'save',
                array(
                    'id'      => 'save_and_close_button',
                    'label'   => Mage::helper('M2ePro')->__('Save And Close'),
                    'onclick' => 'WalmartTemplateCategoryObj.saveAndClose('
                        . '\'' . $this->getUrl('*/*/save', array('_current' => true)) . '\','
                        . ')',
                    'class'   => 'save'
                )
            );
        } else {
            $this->_addButton(
                'save',
                array(
                    'id'      => 'save_button',
                    'label'   => Mage::helper('M2ePro')->__('Save'),
                    'onclick' => 'WalmartTemplateCategoryObj.save_click('
                        . '\'\','
                        . '\'' . $this->getSaveConfirmationText() . '\','
                        . '\'' . Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_CATEGORY . '\''
                        . ')',
                    'class'   => 'save'
                )
            );

            $this->_addButton(
                'save_and_continue',
                array(
                    'id'      => 'save_and_continue_button',
                    'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick' => 'WalmartTemplateCategoryObj.save_and_edit_click('
                        . '\'\','
                        . 'undefined,'
                        . '\'' . $this->getSaveConfirmationText() . '\','
                        . '\'' . Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid::TEMPLATE_CATEGORY . '\''
                        . ')',
                    'class'   => 'save'
                )
            );
        }
    }

    //########################################

    protected function isEditMode()
    {
        $templateModel = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        return $templateModel && $templateModel->getId();
    }

    //########################################
}
