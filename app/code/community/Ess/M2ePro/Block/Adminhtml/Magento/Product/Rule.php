<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Product_Rule
    extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    protected function _prepareForm()
    {
        /** @var Ess_M2ePro_Model_Magento_Product_Rule $model */
        $model = $this->getData('rule_model');
        $storeId = $model->getStoreId();
        $prefix = $model->getPrefix();

        $form = new Varien_Data_Form();
        $form->setHtmlId($prefix);

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('M2ePro/magento/product/rule.phtml')
            ->setNewChildUrl(
                $this->getUrl(
                    '*/adminhtml_general/magentoRuleGetNewConditionHtml',
                    array(
                        'prefix' => $prefix,
                        'store' => $storeId,
                    )
                )
            );

        $fieldset = $form->addFieldset($prefix, array())->setRenderer($renderer);

        $fieldset->addField($prefix . '_field', 'text', array(
            'name' => 'conditions' . $prefix,
            'label' => Mage::helper('M2ePro')->__('Conditions'),
            'title' => Mage::helper('M2ePro')->__('Conditions'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    // ########################################
}