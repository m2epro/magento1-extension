<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_InvoicesAndShipments_Form extends
    Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    protected function _prepareForm()
    {
        $formData = $this->getFormData();

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'action'  => '#',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $fieldset = $form->addFieldset(
            'invoices',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Invoices'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'create_magento_invoice',
            'select',
            array(
                'label'   => Mage::helper('M2ePro')->__('Magento Invoice Creation') . ':',
                'title'   => Mage::helper('M2ePro')->__('Magento Invoice Creation'),
                'name'    => 'create_magento_invoice',
                'options' => array(
                    0 => Mage::helper('M2ePro')->__('Disabled'),
                    1 => Mage::helper('M2ePro')->__('Enabled'),
                ),
                'tooltip' => Mage::helper('M2ePro')->__(
                    <<<HTML
Enable to automatically create Magento Invoices when payment is completed.
HTML
                )
            )
        );

        $fieldset = $form->addFieldset(
            'shipments',
            array(
                'legend'      => Mage::helper('M2ePro')->__('Shipments'),
                'collapsable' => false
            )
        );

        $fieldset->addField(
            'create_magento_shipment',
            'select',
            array(
                'label'              => Mage::helper('M2ePro')->__('Magento Shipment Creation') . ':',
                'title'              => Mage::helper('M2ePro')->__('Magento Shipment Creation'),
                'name'               => 'create_magento_shipment',
                'options'            => array(
                    0 => Mage::helper('M2ePro')->__('Disabled'),
                    1 => Mage::helper('M2ePro')->__('Enabled'),
                ),
                'after_element_html' => Mage::helper('M2ePro')->__(
                    <<<HTML
<span>
    <img class="tool-tip-image"
     style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/tool-tip-icon.png')}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$this->getSkinUrl('M2ePro/images/help.png')}" />
        <span style="color:gray;">
           Enable to automatically create Shipment when shipping is completed.
        </span>
    </span>
</span>
<div style="padding: 15px 0;">
    <hr style="border: 1px solid silver; border-bottom: none;">
</div>
HTML
                )
            )
        );

        $fieldset->addField(
            'skip_evtin',
            'select',
            array(
                'label'   => Mage::helper('M2ePro')->__('Skip eVTN'),
                'title'   => Mage::helper('M2ePro')->__('Skip eVTN'),
                'name'    => 'skip_evtin',
                'options' => array(
                    0 => Mage::helper('M2ePro')->__('No'),
                    1 => Mage::helper('M2ePro')->__('Yes'),
                ),
                'tooltip' => Mage::helper('M2ePro')->__(
                    <<<TEXT
Set <b>Yes</b> if you want to exclude 
<a href="%url%" target="_blank">eVTN</a> from your Magento orders.
TEXT
                    ,
                    Mage::helper('M2ePro/Module_Support')->getKnowledgeBaseUrl('1608273')
                )
            )
        );

        $form->setValues($formData);

        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    <<<HTML
    <p>Under this tab, you can set M2E Pro to automatically create invoices and shipments in your Magento.
     To do that, keep Magento <i>Invoice/Shipment Creation</i> options enabled.</p>
HTML
                ),
                'title'   => Mage::helper('M2ePro')->__('Invoices & Shipments')
            )
        );

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################

    protected function getFormData()
    {
        $formData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            ? Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->toArray()
            : array();

        /** @var Ess_M2ePro_Model_Ebay_Account_Builder $defaults */
        $defaults = Mage::getModel('M2ePro/Ebay_Account_Builder')->getDefaultData();

        return array_merge($defaults, $formData);
    }

    //########################################
}
