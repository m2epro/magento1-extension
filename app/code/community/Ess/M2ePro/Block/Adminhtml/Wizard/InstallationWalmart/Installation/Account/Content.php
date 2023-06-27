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
            'account_details',
            array()
        );

        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplacesCollection */
        $marketplacesCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Marketplace')
            ->addFieldToFilter('developer_key', array('notnull' => true))
            ->setOrder('sorder', 'ASC');

        $marketplaces = array(
            array(
                'value' => '',
                'label' => ''
            )
        );

        foreach ($marketplacesCollection->getItems() as $marketplace) {
            $marketplaces[$marketplace['id']] = $marketplace['title'];
        }

        $fieldset->addField(
            'marketplace_id',
            'select',
            array(
                'label'    => Mage::helper('M2ePro')->__('Marketplace'),
                'name'     => 'marketplace_id',
                'required' => true,
                'values'   => $marketplaces,
                'onchange' => 'InstallationWalmartWizardObj.changeMarketplace(this.value);'
            )
        );

        $marketplaceUS = Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US;
        $marketplaceCA = Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA;

        $fieldset->addField(
            'marketplaces_register_url_ca',
            'link',
            array(
                'label'  => '',
                'href'   => Mage::helper('M2ePro/Component_Walmart')->getRegisterUrl($marketplaceCA),
                'target' => '_blank',
                'style'  => 'margin-left: 210px;',
                'value'  => Mage::helper('M2ePro')->__('Get Access Data'),
                'class'  => "marketplace-required-field marketplace-required-field-id{$marketplaceCA}",
            )
        );

        $fieldset->addField(
            'marketplaces_register_url_us',
            'link',
            array(
                'label'  => '',
                'href'   => Mage::helper('M2ePro/Component_Walmart')->getRegisterUrl($marketplaceUS),
                'target' => '_blank',
                'style'  => 'margin-left: 210px;',
                'value'  => Mage::helper('M2ePro')->__('Get Access Data'),
                'class'  => "marketplace-required-field marketplace-required-field-id{$marketplaceUS}",
            )
        );

        $fieldset->addField(
            'consumer_id',
            'text',
            array(
                'container_id' => 'marketplaces_consumer_id_container',
                'name'         => 'consumer_id',
                'label'        => Mage::helper('M2ePro')->__('Consumer ID'),
                'required'     => true,
                'class'        => "marketplace-required-field marketplace-required-field-id{$marketplaceCA}",
                'tooltip'      => Mage::helper('M2ePro')->__('A unique seller identifier on the website.'),
            )
        );

        $fieldset->addField(
            'private_key',
            'textarea',
            array(
                'container_id' => 'marketplaces_private_key_container',
                'name'         => 'private_key',
                'label'        => Mage::helper('M2ePro')->__('Private Key'),
                'required'     => true,
                'class'        => "M2ePro-marketplace-merchant marketplace-required-field "
                    . "marketplace-required-field-id{$marketplaceCA}",
                'tooltip'      => Mage::helper('M2ePro')->__(
                    'Walmart Private Key generated from your Seller Center Account.'
                )
            )
        );

        $fieldset->addField(
            'client_id',
            'text',
            array(
                'container_id' => 'marketplaces_client_id_container',
                'name'         => 'client_id',
                'label'        => Mage::helper('M2ePro')->__('Client ID'),
                'class'        => "marketplace-required-field marketplace-required-field-id{$marketplaceUS}",
                'required'     => true,
                'tooltip'      => Mage::helper('M2ePro')->__('A Client ID retrieved to get an access token.')
            )
        );

        $fieldset->addField(
            'client_secret',
            'textarea',
            array(
                'container_id' => 'marketplaces_client_secret_container',
                'name'         => 'client_secret',
                'label'        => Mage::helper('M2ePro')->__('Client Secret'),
                'required'     => true,
                'class'        => "M2ePro-marketplace-merchant marketplace-required-field "
                                . "marketplace-required-field-id{$marketplaceUS}",
                'tooltip'      => Mage::helper('M2ePro')->__('A Client Secret key retrieved to get an access token.')
            )
        );

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

        return parent::_toHtml() . $javascript;
    }

    //########################################
}
