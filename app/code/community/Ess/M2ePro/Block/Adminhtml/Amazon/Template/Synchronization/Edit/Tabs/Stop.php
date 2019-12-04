<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Synchronization_Edit_Tabs_Stop
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateSynchronizationEditTabsStop');
        $this->setTemplate('M2ePro/amazon/template/synchronization/stop.phtml');
    }

    //########################################

    public function getAdvancedRulesBlock()
    {
        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX,
                'use_custom_options' => true
            )
        );

        $formData = $this->getData('form_data');
        if (!empty($formData['stop_advanced_rules_filters'])) {
            $ruleModel->loadFromSerialized($formData['stop_advanced_rules_filters']);
        }

        $ruleBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_magento_product_rule')
            ->setData(array('rule_model' => $ruleModel));

        return $ruleBlock;
    }

    //########################################
}
