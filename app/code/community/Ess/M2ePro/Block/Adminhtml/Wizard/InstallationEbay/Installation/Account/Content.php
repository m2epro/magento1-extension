<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_Account_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationEbayAccountContent');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
On this step, you should link your eBay Account with your M2E Pro.<br/><br/>
You can proceed with both Live and Sandbox eBay Environments. Live environment is set by default.
HTML
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'action'  => '',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'wizard_ebay_accounts',
            array()
        );

        $url = 'https://scgi.ebay.com/ws/eBayISAPI.dll?RegisterEnterInfo&bizflow=2';
        $fieldset->addField(
            'message',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::MESSAGES,
            array(
                'messages' => array(
                    array(
                        'type'    => Mage_Core_Model_Message::NOTICE,
                        'content' => Mage::helper('M2ePro')->__(
                            'If you do not have an existing account, you can click
                            <a href="%url%" target="_blank">here</a> to register one.',
                            $url
                        )
                    )
                )
            )
        );

        $fieldset->addField(
            'mode',
            'radios',
            array(
                'label'  => Mage::helper('M2ePro')->__('What the Type of Account do You Want to Onboard?'),
                'class'  => 'account-mode-choose',
                'name'   => 'mode',
                'values' => array(
                    array(
                        'value' => 'production',
                        'label' => Mage::helper('M2ePro')->__('Live Account')
                    ),
                    array(
                        'value' => 'sandbox',
                        'label' => Mage::helper('M2ePro')->__('Sandbox Account')
                    ),
                    array(
                        'value' => '',
                        'label' => ''
                    ),
                ),
                'value' => 'production'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################
}
