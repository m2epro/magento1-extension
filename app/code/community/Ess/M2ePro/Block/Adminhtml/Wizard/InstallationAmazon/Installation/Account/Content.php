<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_Account_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationAmazonAccountContent');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
On this step, you should link your Amazon Account with your M2E Pro.<br/><br/>
Please, select the Marketplace you are going to sell on and click on Continue button.
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
            'wizard_amazon_marketplaces',
            array()
        );

        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplacesCollection */
        $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
            ->setOrder('group_title', 'ASC')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC');

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
                'label'    => Mage::helper('M2ePro')->__('What the Marketplace do You Want to Onboard?'),
                'class'    => 'marketplace-mode-choose',
                'name'     => 'marketplace_id',
                'values'   => $marketplaces,
                'onchange' => 'InstallationAmazonWizardObj.marketplaceChange()'
            )
        );

        $fieldset->addField(
            'amazon_wizard_installation_account_manual_authorization',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::MESSAGES,
            array(
                'messages' => array(
                    array(
                        'content' => Mage::helper('M2ePro')->__(
                            'For providing access to Amazon Account click the "Get Access Data" link.
                            You will be redirected to the Amazon Website.<br /><br />
                            Sign-in and complete steps of getting access for M2E Pro:<br /><br />
                            <ul style="margin-left: 25px;">
                                <li>Select - \'I want to use an application to access
                                my Amazon Seller Account with MWS.\'</li>
                                <li>Fill in Application Name and Application\'s Developer Account Number, which you
                                can find in the Marketplaces Section on the current Page.</li>
                                <li>Accept the Amazon MWS License Agreement.</li>
                                <li>Copy generated "Merchant ID" / "MWS Auth Token" and paste it in the corresponding
                                fields of the current Page.</li>
                            </ul>'
                        ),
                        'type' => Mage_Core_Model_Message::NOTICE,
                    ),
                ),
            )
        )->setClass('manual-authorization');

        $fieldset = $form->addFieldset(
            'manual_authorization_marketplace',
            array(
                'class' => 'manual-authorization'
            )
        );

        $fieldset->addField(
            'manual_authorization_marketplace_application_name',
            'label',
            array(
                'container_id' => 'manual_authorization_marketplace_application_name_container',
                'label'        => Mage::helper('M2ePro')->__('Application Name'),
                'value'        => Mage::helper('M2ePro/Component_Amazon')->getApplicationName(),
                'class'        => 'manual-authorization',
            )
        );

        foreach ($marketplacesCollection->getItems() as $marketplace) {
            if ($marketplace['is_automatic_token_retrieving_available']) {
                continue;
            }

            $fieldset->addField(
                'manual_authorization_marketplace_developer_key_' . $marketplace['id'],
                'label',
                array(
                    'container_id' => 'manual_authorization_marketplace_developer_key_container_' . $marketplace['id'],
                    'label'        => Mage::helper('M2ePro')->__('Developer Account Number'),
                    'value'        => $marketplace['developer_key'],
                    'class'        => 'manual-authorization',
                )
            );

            $fieldset->addField(
                'manual_authorization_marketplace_register_url_' . $marketplace['id'],
                'link',
                array(
                    'container_id' => 'manual_authorization_marketplace_register_url_container_' . $marketplace['id'],
                    'label'        => '',
                    'href'         => Mage::helper('M2ePro/Component_Amazon')->getRegisterUrl($marketplace['id']),
                    'onclick'      => '',
                    'target'       => '_blank',
                    'value'        => Mage::helper('M2ePro')->__('Get Access Data'),
                    'class'        => 'manual-authorization',
                )
            );

            $fieldset->addField(
                'manual_authorization_marketplace_merchant_id_'.$marketplace['id'],
                'text',
                array(
                    'container_id' => 'manual_authorization_marketplace_merchant_id_container_' . $marketplace['id'],
                    'label'        => Mage::helper('M2ePro')->__('Merchant ID'),
                    'name'         => 'manual_authorization_marketplace_merchant_id_'.$marketplace['id'],
                    'style'        => 'width: 50%',
                    'required'     => true,
                    'class'        => 'manual-authorization M2ePro-marketplace-merchant',
                    'tooltip'      => Mage::helper('M2ePro')->__(
                        'Paste generated Merchant ID from Amazon. (It must look like: A15UFR7CZVW5YA).'
                    )
                )
            );

            $fieldset->addField(
                'manual_authorization_marketplace_token_'.$marketplace['id'],
                'text',
                array(
                    'container_id' => 'manual_authorization_marketplace_token_container_' . $marketplace['id'],
                    'label'        => Mage::helper('M2ePro')->__('MWS Auth Token'),
                    'name'         => 'manual_authorization_marketplace_token_'.$marketplace['id'],
                    'style'        => 'width: 50%',
                    'required'     => true,
                    'class'        => 'manual-authorization M2ePro-marketplace-merchant',
                    'tooltip'      => Mage::helper('M2ePro')->__(
                        'Paste generated MWS Auth Token from Amazon.
                        (It must look like: amzn.mws.bna3f75c-a683-49c7-6da0-749y33313dft).'
                    )
                )
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _toHtml()
    {
        $javascript = <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        $('marketplace_id').simulate('change');
    });

</script>
HTML;

        return parent::_toHtml() . $javascript;
    }

    //########################################
}
