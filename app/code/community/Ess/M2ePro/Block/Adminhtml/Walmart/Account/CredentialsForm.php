<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_CredentialsForm extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $withTitle = $this->getData('with_title');
        $withButton = $this->getData('with_button');
        $formId = $this->getData('form_id');

        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Account_CredentialsFormFactory $factoryBlock */
        $factoryBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_account_credentialsFormFactory');

        $form = $factoryBlock->create($withTitle, $withButton, $formId);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
