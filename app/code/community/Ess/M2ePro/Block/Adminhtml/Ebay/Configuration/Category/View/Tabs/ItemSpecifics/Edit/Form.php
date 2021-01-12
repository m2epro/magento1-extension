<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Dictionary as RendererDictionary;
use Ess_M2ePro_Model_Ebay_Template_Category_Specific as Specific;

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_ItemSpecifics_Edit_Form extends
    Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/saveTemplateCategorySpecifics'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $templateId = $this->getRequest()->getParam('template_id');

        $form->addField(
            'template_id',
            'hidden',
            array(
                'name' => 'template_id',
                'value' => $templateId
            )
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category')->load($templateId);

        $templateSpecifics = $template->getSpecifics();
        $dictionarySpecifics = (array)Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSpecifics(
            $template->getCategoryId(), $template->getMarketplaceId()
        );

        foreach ($dictionarySpecifics as &$dictionarySpecific) {
            foreach ($templateSpecifics as $templateSpecific) {
                if ($dictionarySpecific['title'] == $templateSpecific['attribute_title']) {
                    $dictionarySpecific['template_specific'] = $templateSpecific;
                    continue;
                }
            }
        }

        unset($dictionarySpecific);

        if (!empty($dictionarySpecifics)) {
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

            /** @var RendererDictionary $renderer */
            $renderer = $this->getLayout()
                ->createBlock('M2ePro/Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Renderer_Dictionary');
            $fieldset->addField(
                'dictionary_specifics',
                'dictionary_specifics',
                array(
                    'specifics' => $dictionarySpecifics,
                )
            )->setRenderer($renderer);
        }

        $templateCustomSpecifics = array();
        foreach ($templateSpecifics as $templateSpecific) {
            if ($templateSpecific['mode'] == Specific::MODE_CUSTOM_ITEM_SPECIFICS) {
                $templateCustomSpecifics[] = $templateSpecific;
            }
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
                'specifics' => $templateCustomSpecifics,
            )
        )->setRenderer($renderer);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->add('Item Specifics cannot have the same Labels.');

        Mage::helper('M2ePro/View')->getJsPhpRenderer()
            ->addClassConstants('Ess_M2ePro_Model_Ebay_Template_Category_Specific');

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    EbayTemplateCategorySpecificsObj = new EbayTemplateCategorySpecifics();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################
}
