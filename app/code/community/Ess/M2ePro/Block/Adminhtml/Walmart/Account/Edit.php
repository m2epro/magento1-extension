<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_account';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit %component_name% Account", $componentName);
            $headerTextAdd = Mage::helper('M2ePro')->__("Add %component_name% Account", $componentName);
        } else {
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit Account");
            $headerTextAdd = Mage::helper('M2ePro')->__("Add Account");
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            $this->_headerText = $headerTextEdit;
            $this->_headerText .= ' "'.$this->escapeHtml(
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
                ->getTitle()
            ).'"';
        } else {
            $this->_headerText = $headerTextAdd;
        }

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

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isActive('installationWalmart') &&
            $wizardHelper->getStep('installationWalmart') == 'account') {
            // ---------------------------------------
            $this->_addButton(
                'save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'WalmartAccountObj.save_and_edit_click(\'\',\'walmartAccountEditTabs\')',
                'class'     => 'save'
                )
            );
            // ---------------------------------------

            if ($this->getRequest()->getParam('id')) {
                // ---------------------------------------
                $url = $this->getUrl('*/adminhtml_walmart_account/new', array('wizard' => true));
                $this->_addButton(
                    'add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''. $url .'\')',
                    'class'     => 'add_new_account'
                    )
                );
                // ---------------------------------------

                // ---------------------------------------
                $this->_addButton(
                    'close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'WalmartAccountObj.completeStep();',
                    'class'     => 'close'
                    )
                );
                // ---------------------------------------
            }
        } else {
            if ((bool)$this->getRequest()->getParam('close_on_save', false)) {
                if ($this->getRequest()->getParam('id')) {
                    $this->_addButton(
                        'save', array(
                        'label'     => Mage::helper('M2ePro')->__('Save And Close'),
                        'onclick'   => 'WalmartAccountObj.saveAndClose()',
                        'class'     => 'save'
                        )
                    );
                } else {
                    $this->_addButton(
                        'save_and_continue', array(
                        'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                        'onclick'   => 'WalmartAccountObj.save_and_edit_click(\'\',\'walmartAccountEditTabs\')',
                        'class'     => 'save'
                        )
                    );
                }

                return;
            }

            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('list');
            $this->_addButton(
                'back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'WalmartAccountObj.back_click(\''. $url .'\')',
                'class'     => 'back'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
            ) {
                // ---------------------------------------
                $accountId = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId();
                $this->_addButton(
                    'delete', array(
                    'label'     => Mage::helper('M2ePro')->__('Delete'),
                    'onclick'   => "WalmartAccountObj.delete_click({$accountId})",
                    'class'     => 'delete M2ePro_delete_button'
                    )
                );
                // ---------------------------------------
            }

            // ---------------------------------------
            $this->_addButton(
                'save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'WalmartAccountObj.save_click()',
                'class'     => 'save'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            $this->_addButton(
                'save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'WalmartAccountObj.save_and_edit_click(\'\',\'walmartAccountEditTabs\')',
                'class'     => 'save'
                )
            );
            // ---------------------------------------
        }
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    AccountObj = new Account();
    WalmartAccountObj = new WalmartAccount();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################
}
