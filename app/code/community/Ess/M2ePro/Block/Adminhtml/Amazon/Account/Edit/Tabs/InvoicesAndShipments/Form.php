<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as AmazonAccount;

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_InvoicesAndShipments_Form extends
    Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    protected function _prepareForm()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');

        $formData = $this->getFormData();

        $form = new Varien_Data_Form(
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

        if ($account->getChildObject()->getMarketplace()->getChildObject()->isVatCalculationServiceAvailable()) {
            $options = array(
                AmazonAccount::AUTO_INVOICING_DISABLED => Mage::helper('M2ePro')->__('Disabled')
            );
            if ($account->getChildObject()->getMarketplace()->getChildObject()->isUploadInvoicesAvailable()) {
                $options[AmazonAccount::AUTO_INVOICING_UPLOAD_MAGENTO_INVOICES] =
                    Mage::helper('M2ePro')->__('Upload Magento Invoices');
            }

            $options[AmazonAccount::AUTO_INVOICING_VAT_CALCULATION_SERVICE] =
                Mage::helper('M2ePro')->__('Use VAT Calculation Service');

            $fieldset->addField(
                'auto_invoicing',
                'select',
                array(
                    'label'   => Mage::helper('M2ePro')->__('Invoice Uploading to Amazon') . ':',
                    'title'   => Mage::helper('M2ePro')->__('Invoice Uploading to Amazon'),
                    'name'    => 'auto_invoicing',
                    'options' => $options,
                    'value'   => $formData['auto_invoicing']
                )
            );

            $fieldset->addField(
                'invoice_generation',
                'select',
                array(
                    'label'              => Mage::helper('M2ePro')->__('VAT Invoice Creation') . ':',
                    'title'              => Mage::helper('M2ePro')->__('VAT Invoice Creation'),
                    'name'               => 'invoice_generation',
                    'class'              => 'M2ePro-required-when-visible M2ePro-is-ready-for-document-generation',
                    'required'           => true,
                    'values'             => array(
                        ''                                             => '',
                        AmazonAccount::INVOICE_GENERATION_BY_AMAZON    =>
                            Mage::helper('M2ePro')->__('I want Amazon to generate VAT Invoices'),
                        AmazonAccount::INVOICE_GENERATION_BY_EXTENSION =>
                            Mage::helper('M2ePro')->__('I will upload my own Invoices'),
                    ),
                    'value'              => '',
                    'after_element_html' => <<<HTML
<tr>
    <td colspan="6" style="padding: 10px 0">
        <hr style="border: 1px solid silver; border-bottom: none;">
    </td>
</tr>
HTML

                )
            );
        }

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
                'after_element_html' => Mage::helper('M2ePro')->__(
                    <<<HTML
<span>
    <img class="tool-tip-image"
     style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/tool-tip-icon.png')}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px; background: #E3E3E3;">
        <img src="{$this->getSkinUrl('M2ePro/images/help.png')}" />
        <span style="color:gray;">
           Enable to automatically create Magento Invoices when order status is Unshipped/Partially Shipped.
        </span>
    </span>
</span>
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
                'label'   => Mage::helper('M2ePro')->__('Magento Shipment Creation') . ':',
                'title'   => Mage::helper('M2ePro')->__('Magento Shipment Creation'),
                'name'    => 'create_magento_shipment',
                'options' => array(
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
HTML
                )
            )
        );

        $form->setValues($formData);

        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _prepareLayout()
    {
        $formData = $this->getFormData();

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
if ($('auto_invoicing')) {
    $('auto_invoicing')
        .observe('change', AmazonAccountObj.autoInvoicingModeChange)
        .simulate('change');
    
    $('invoice_generation').removeClassName('required-entry');
    $('create_magento_invoice').value = {$formData['create_magento_invoice']};
}
JS
            ,
            2
        );

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $helpText = Mage::helper('M2ePro')->__(
            <<<HTML
    <p>Under this tab, you can enable Magento <i>Invoice/Shipment Creation</i> if you want M2E Pro to automatically 
    create invoices and shipments in your Magento.</p>
HTML
        );

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');
        if ($account->getChildObject()->getMarketplace()->getChildObject()->isUploadInvoicesAvailable()) {
            $helpText .= Mage::helper('M2ePro')->__(
                <<<HTML
    <p>Also, you can set up an <i>Automatic Invoice Uploading</i> to Amazon. Read the <a href="%url%" 
    target="_blank">article</a> for more details.</p>
HTML
                ,
                Mage::helper('M2ePro/Module_Support')->getHowToGuideUrl('1602134')
            );
        }

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => $helpText,
                'title'   => Mage::helper('M2ePro')->__('Invoices & Shipments')
            )
        );

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################

    protected function getFormData()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');

        $formData = $account ? $account->toArray() : array();
        $defaults = Mage::getModel('M2ePro/Amazon_Account_Builder')->getDefaultData();

        return array_merge($defaults, $formData);
    }

    //########################################
}
