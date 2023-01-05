<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateSellingFormatEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/template/selling_format/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Model_Template_SellingFormat $templateModel */
        $templateModel = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // ---------------------------------------
        $marketplaces = Mage::helper('M2ePro/Component_Walmart')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->setData('marketplaces', $marketplaces['items']);
        // ---------------------------------------

        // ---------------------------------------
        $marketplaceLocked = false;

        if ($templateModel && $templateModel->getId()) {
            $marketplaceLocked = $templateModel->getChildObject()->isLockedMarketplace();
        }

        $this->setData('marketplace_locked', $marketplaceLocked);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'onclick' => 'WalmartTemplateSellingFormatObj.addRow(\'fixed\');',
                'class' => 'add add_discount_rule_button'
                )
            );
        $this->setChild('add_custom_value_discount_rule_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Remove'),
                'onclick' => 'WalmartTemplateSellingFormatObj.removeRow(this);',
                'class' => 'delete icon-btn remove_discount_rule_button'
                )
            );
        $this->setChild('remove_discount_rule_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 10; $i++) {
            $button = $this->getMultiElementButton('attributes', $i);
            $this->setChild("select_attributes_for_attributes_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'onclick' => 'WalmartTemplateSellingFormatObj.addPromotionsPriceRow();',
                'class' => 'add add_promotion_price_button'
                )
            );
        $this->setChild('add_promotion_price_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Remove'),
                'onclick' => 'WalmartTemplateSellingFormatObj.removePromotionsPriceRow(this);',
                'class' => 'delete icon-btn remove_promotion_price_button'
                )
            );
        $this->setChild('remove_promotion_price_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'onclick' => 'WalmartTemplateSellingFormatObj.addRow();',
                'class' => 'add add_shipping_override_rule_button'
                )
            );
        $this->setChild('add_shipping_override_rule_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Remove'),
                'onclick' => 'WalmartTemplateSellingFormatObj.removeRow(this);',
                'class' => 'delete icon-btn remove_shipping_override_rule_button'
                )
            );
        $this->setChild('remove_shipping_override_rule_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function getMultiElementButton($type, $index)
    {
        $onClick = <<<JS
        AttributeObj.appendToText('select_attributes_for_{$type}_{$index}', '{$type}_value_{$index}');
        WalmartTemplateSellingFormatObj.multi_element_keyup('{$type}', $('{$type}_value_{$index}'));
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => $onClick,
            'class'   => "select_attributes_for_{$type}_{$index}_button"
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
    }

    //########################################

    public function getWeightUnits()
    {
        return array('LB');
    }

    public function getShippingOverrideRegionsUs()
    {
        return array(
            'STREET_48_STATES'        => 'Street 48 States',
            'PO_BOX_48_STATES'        => 'PO Box 48 States',
            'STREET_AK_AND_HI'        => 'Street AK and HI',
            'PO_BOX_AK_AND_HI'        => 'PO Box AK and HI',
            'STREET_US_PROTECTORATES' => 'Street US Protectorates',
            'PO_BOX_US_PROTECTORATES' => 'PO Box US Protectorates',
            'APO_FPO'                 => 'APO FPO'
        );
    }

    public function getShippingOverrideMethodsUs()
    {
        return array(
            'VALUE'                    => 'Value',
            'STANDARD'                 => 'Standard',
            'EXPEDITED'                => 'Expedited',
            'FREIGHT'                  => 'Freight',
            'ONE_DAY'                  => 'One day',
            'FREIGHT_WITH_WHITE_GLOVE' => 'Freight with white glove'
        );
    }

    //########################################

    public function getShippingOverrideRegionsCanada()
    {
        return array(
            'STREET_URBAN_ONTEAST' => 'Street Urban Ontario East',
            'POBOX_URBAN_ONTEAST'  => 'PO Box Urban Ontario East',
            'STREET_URBAN_QUEBEC'  => 'Street Urban Quebec',
            'POBOX_URBAN_QUEBEC'   => 'PO Box Urban Quebec',
            'STREET_URBAN_WEST'    => 'Street Urban West',
            'POBOX_URBAN_WEST'     => 'PO Box Urban West',
            'STREET_REMOTE_QUEBEC' => 'Street Remote Quebec',
            'POBOX_REMOTE_QUEBEC'  => 'PO Box Remote Quebec',
            'STREET_REMOTE_CANADA' => 'Street Remote Canada',
            'POBOX_REMOTE_CANADA'  => 'PO Box Remote Canada',
        );
    }

    public function getShippingOverrideMethodsCanada()
    {
        return array(
            'STANDARD'  => 'Standard',
            'EXPEDITED' => 'Expedited',
        );
    }

    //########################################
}
