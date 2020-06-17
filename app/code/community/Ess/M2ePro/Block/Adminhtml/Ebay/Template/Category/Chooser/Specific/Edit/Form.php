<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Edit_Form extends
    Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => '',
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $formData = $this->getData('form_data');

        if (!empty($formData['dictionary_specifics'])) {
            $fieldset = $form->addFieldset(
                'dictionary',
                array(
                    'legend' => Mage::helper('M2ePro')->__('eBay Specifics'),
                    'collapsable' => false
                )
            );

            $fieldset->addType(
                'dictionary_specifics',
                'Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Dictionary'
            );

            /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Dictionary $renderer */
            $renderer = $this->getLayout()
                ->createBlock('M2ePro/Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Dictionary');
            $fieldset->addField(
                'dictionary_specifics',
                'dictionary_specifics',
                array(
                    'specifics' => $formData['dictionary_specifics'],
                )
            )->setRenderer($renderer);
        }

        $fieldset = $form->addFieldset(
            'custom',
            array(
                'legend' => Mage::helper('M2ePro')->__('Additional Specifics'),
                'collapsable' => false
            )
        );

        $fieldset->addType(
            'custom_specifics',
            'Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Custom'
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Custom $renderer */
        $renderer = $this->getLayout()
            ->createBlock('M2ePro/Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Custom');
        $fieldset->addField(
            'custom_specifics',
            'custom_specifics',
            array(
                'specifics' => $formData['template_custom_specifics'],
            )
        )->setRenderer($renderer);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################
}