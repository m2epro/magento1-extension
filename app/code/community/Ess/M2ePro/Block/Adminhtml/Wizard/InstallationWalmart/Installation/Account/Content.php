<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Account_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationWalmartAccountContent');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
<div>
    Under this section, you can link your Walmart account to M2E Pro.
    Read how to <a href="%url%" target="_blank">get the API credentials</a> or register on 
    <a href="https://marketplace-apply.walmart.com/apply?id=00161000012XSxe" target="_blank">Walmart US</a> / 
    <a href="https://marketplace.walmart.ca/apply?q=ca" target="_blank">Walmart CA</a>.
</div>
HTML
                ,
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, 'account-configuration')
            )
        );

        $marketplaceBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_wizard_installationWalmart_installation_account_marketplaceSelector');
        $this->setChild('marketplace_selector', $marketplaceBlock);

        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Account_CredentialsFormFactory $factoryBlock */
        $factoryBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_account_credentialsFormFactory');

        $form = $factoryBlock->create(false, false, 'edit_form');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _toHtml()
    {
        $javascript = <<<HTML
<script type="text/javascript">

    WalmartAccountObj = new WalmartAccount();
    WalmartAccountObj.initTokenValidation();
        
    Event.observe(window, 'load', function() {
        $('marketplace_id').simulate('change');
    });

</script>
HTML;

        return $this->getChildHtml('marketplace_selector') . parent::_toHtml() . $javascript;
    }

    //########################################
}
