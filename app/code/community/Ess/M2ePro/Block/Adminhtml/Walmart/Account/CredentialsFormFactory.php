<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_CredentialsFormFactory extends Mage_Adminhtml_Block_Widget_Form
{

    public function create($withTitle, $withButton, $id)
    {
        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => $id,
                'action'  => '',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'account_details',
            array()
        );

        if ($withTitle) {
            $fieldset->addField(
                'title',
                'text',
                array(
                    'name' => 'title',
                    'class' => 'M2ePro-account-title',
                    'label' => Mage::helper('M2ePro')->__('Title'),
                    'required' => true,
                    'value' => '',
                )
            );
        }

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

        if ($withButton) {
            $fieldset->addField(
                'submit_button',
                'submit',
                array(
                    'value' => Mage::helper('M2ePro')->__('Save'),
                    'style' => 'float: right; width: 30%; margin-right: 10px;',
                    'class' => 'submit action-default action-primary',
                )
            );
        }

        return $form;
    }
}
