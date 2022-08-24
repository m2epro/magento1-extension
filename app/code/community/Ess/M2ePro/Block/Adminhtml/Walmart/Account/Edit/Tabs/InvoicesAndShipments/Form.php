<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_Edit_Tabs_InvoicesAndShipments_Form extends
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
Enable to automatically create Magento Invoices when order status is Unshipped/Partially Shipped.
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
                'after_element_html' => <<<HTML
<span>
    <img class="tool-tip-image"
     style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/tool-tip-icon.png')}" />
    <span class="tool-tip-message" style="display:none; text-align: left; width: 120px;">
        <img src="{$this->getSkinUrl('M2ePro/images/help.png')}" />
        <span>
           Enable to automatically create shipment for the Magento order when 
           the associated order on Channel is shipped.
        </span>
    </span>
</span>
<div style="padding: 15px 0;">
    <hr style="border: 1px solid silver; border-bottom: none;">
</div>
HTML
            )
        );

        $helper = Mage::helper('M2ePro');
        $otherCarriers = empty($formData['other_carriers']) ? array() : Mage::helper('M2ePro')->jsonDecode(
            $formData['other_carriers']
        );
        for ($i = 0; $i < 30; $i++) {
            $code = $url = '';

            if (!empty($otherCarriers[$i])) {
                $code = Mage::helper('M2ePro')->escapeHtml($otherCarriers[$i]['code']);
                $url = Mage::helper('M2ePro')->escapeHtml($otherCarriers[$i]['url']);
            }

            $fieldset->addField(
                'other_carrier_field_' . $i,
                Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
                array(
                    'container_class'    => 'other_carrier',
                    'label'              => Mage::helper('M2ePro')->__('Other Carrier #%number%:', $i + 1),
                    'title'              => Mage::helper('M2ePro')->__('Other Carrier #%number%:', $i + 1),
                    'text'               => <<<HTML
<input id="other_carrier_{$i}"
       type="text"
       name="other_carrier[]"
       value="{$code}"
       style="width: 127.5px;"
       class="input-text"
       onkeyup="window.WalmartAccountObj.otherCarrierKeyup(this)"
       placeholder="{$helper->__('Title')}"
/>
HTML
                    ,
                    'after_element_html' => <<<HTML
<input id="other_carrier_url_{$i}"
       type="text"
       name="other_carrier_url[]"
       value="{$url}"
       style="width: 127.5px; margin-left: 10px;"
       class="input-text"
       onkeyup="window.WalmartAccountObj.otherCarrierUrlKeyup(this)"
       placeholder="{$helper->__('URL')}"
/>
HTML
                    ,
                    'tooltip'            => Mage::helper('M2ePro')->__(
                        <<<TEXT
If you use Other Carrier option on Walmart,
enter a carrier code (unique identifier) and their website URL,
so that your buyers could track shipments.
TEXT
                    )
                )
            );
        }

        $fieldset->addField(
            'other_carrier_actions',
            Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form::CUSTOM_CONTAINER,
            array(
                'text' => <<<HTML
<a id="show_other_carrier_action"
   href="javascript: void(0);"
   onclick="window.WalmartAccountObj.showElement();">
   {$helper->__('Add New')}
</a>
&nbsp;/&nbsp;
<a id="hide_other_carrier_action"
   href="javascript: void(0);"
   onclick="window.WalmartAccountObj.hideElement();">
   {$helper->__('Remove')}
</a>
HTML
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
        Mage::helper('M2ePro/View')->getCssRenderer()->add(
            <<<CSS
    a.action-disabled {
        color: gray !important;
        pointer-events: none; !important;
        text-decoration: none !important;
    }
    a.action-disabled:hover {
        color: gray !important;
        pointer-events: none; !important;
        text-decoration: none !important;
    }
CSS
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
    WalmartAccountObj.otherCarrierInit(30);
JS
            ,
            2
        );

        return parent::_prepareLayout();
    }

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

        /** @var Ess_M2ePro_Model_Walmart_Account_Builder $defaults */
        $defaults = Mage::getModel('M2ePro/Walmart_Account_Builder')->getDefaultData();

        return array_merge($defaults, $formData);
    }

    //########################################
}
