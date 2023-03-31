<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_account';
        $this->_mode = 'edit';
        // ---------------------------------------

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');

        $this->_headerText = Mage::helper('M2ePro')->__("Edit Amazon Account")
            . ' "'.$this->escapeHtml($account->getTitle()).'"';

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

        if ($wizardHelper->isActive('installationAmazon') &&
            $wizardHelper->getStep('installationAmazon') == 'account') {
            // ---------------------------------------
            $this->_addButton(
                'save_and_continue',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick' => 'AmazonAccountObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                    'class'   => 'save'
                )
            );
            // ---------------------------------------

            if ($this->getRequest()->getParam('id')) {
                // ---------------------------------------
                $url = $this->getUrl('*/adminhtml_amazon_account/new', array('wizard' => true));
                $this->_addButton(
                    'add_new_account',
                    array(
                        'label'   => Mage::helper('M2ePro')->__('Add New Account'),
                        'onclick' => 'setLocation(\'' . $url . '\')',
                        'class'   => 'add_new_account'
                    )
                );
                // ---------------------------------------

                // ---------------------------------------
                $this->_addButton(
                    'close',
                    array(
                        'label'   => Mage::helper('M2ePro')->__('Complete This Step'),
                        'onclick' => 'AmazonAccountObj.completeStep();',
                        'class'   => 'close'
                    )
                );
                // ---------------------------------------
            }
        } else {
            if ((bool)$this->getRequest()->getParam('close_on_save', false)) {
                if ($this->getRequest()->getParam('id')) {
                    $this->_addButton(
                        'save',
                        array(
                            'label'   => Mage::helper('M2ePro')->__('Save And Close'),
                            'onclick' => 'AmazonAccountObj.saveAndClose()',
                            'class'   => 'save'
                        )
                    );
                } else {
                    $this->_addButton(
                        'save_and_continue',
                        array(
                            'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                            'onclick' => 'AmazonAccountObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                            'class'   => 'save'
                        )
                    );
                }

                return;
            }

            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('list');
            $this->_addButton(
                'back',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Back'),
                    'onclick' => 'AmazonAccountObj.back_click(\'' . $url . '\')',
                    'class'   => 'back'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            if ($account && $account->getId()) {
                // ---------------------------------------
                $accountId = $account->getId();
                $this->_addButton(
                    'delete',
                    array(
                        'label'   => Mage::helper('M2ePro')->__('Delete'),
                        'onclick' => "AmazonAccountObj.delete_click({$accountId})",
                        'class'   => 'delete M2ePro_delete_button'
                    )
                );
                // ---------------------------------------
            }

            // ---------------------------------------
            $this->_addButton(
                'save',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Save'),
                    'onclick' => 'AmazonAccountObj.save_click()',
                    'class'   => 'save'
                )
            );
            // ---------------------------------------

            // ---------------------------------------
            $this->_addButton(
                'save_and_continue',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick' => 'AmazonAccountObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                    'class'   => 'save',
                    'id'      => 'save_and_continue',
                )
            );
            // ---------------------------------------
        }
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'is_ready_for_document_generation' => Mage::helper('M2ePro')->__(<<<HTML
    To use this option, <i>Store Name</i> and <i>Store Contact Information</i> must be provided under <i>System > 
    Configuration > General > General > Store Information</i>. 
    Read more <a href="%url%" target="_blank">here</a>.
HTML
                    ,
                    Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/articles/9000219394')
                )
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    AmazonAccountObj = new AmazonAccount();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################
}
