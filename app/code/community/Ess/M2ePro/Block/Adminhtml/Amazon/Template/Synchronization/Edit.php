<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Synchronization_Edit
    extends Ess_M2ePro_Block_Adminhtml_Amazon_Template_Edit
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateSynchronizationEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template_synchronization';
        $this->_mode = 'edit';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();

            if ($this->isEditMode()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit %component_name% Synchronization Policy "%template_title%"',
                    $componentName,
                    $this->escapeHtml(Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Add %component_name% Synchronization Policy',
                    $componentName
                );
            }
        } else {
            if ($this->isEditMode()) {
                $this->_headerText = Mage::helper('M2ePro')->__(
                    'Edit Synchronization Policy "%template_title%"',
                    $this->escapeHtml(Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle())
                );
            } else {
                $this->_headerText = Mage::helper('M2ePro')->__('Add Synchronization Policy');
            }
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = Mage::helper('M2ePro')->getBackUrl('list');
        $this->_addButton(
            'back',
            array(
                'id'      => 'back_button',
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'AmazonTemplateSynchronizationObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );

        $isSaveAndClose = (bool)$this->getRequest()->getParam('close_on_save', false);

        if (!$isSaveAndClose && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {
            $this->_addButton(
                'duplicate',
                array(
                    'id'      => 'duplicate_button',
                    'label'   => Mage::helper('M2ePro')->__('Duplicate'),
                    'onclick' => 'AmazonTemplateSynchronizationObj.duplicate_click'
                        . '(\'amazon-template-synchronization\')',
                    'class'   => 'add M2ePro_duplicate_button'
                )
            );

            $this->_addButton(
                'delete',
                array(
                    'id'      => 'delete_button',
                    'label'   => Mage::helper('M2ePro')->__('Delete'),
                    'onclick' => 'AmazonTemplateSynchronizationObj.delete_click()',
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
                    'onclick' => 'AmazonTemplateSynchronizationObj.saveAndClose('
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
                    'onclick' => 'AmazonTemplateSynchronizationObj.save_click('
                        . '\'\','
                        . '\'' . $this->getSaveConfirmationText() . '\','
                        . '\'' . Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SYNCHRONIZATION . '\''
                        . ')',
                    'class'   => 'save'
                )
            );

            $this->_addButton(
                'save_and_continue',
                array(
                    'id'      => 'save_and_continue_button',
                    'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick' => 'AmazonTemplateSynchronizationObj.save_and_edit_click('
                        . '\'\','
                        . 'undefined,'
                        . '\'' . $this->getSaveConfirmationText() . '\','
                        . '\'' . Ess_M2ePro_Block_Adminhtml_Amazon_Template_Grid::TEMPLATE_SYNCHRONIZATION . '\''
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
