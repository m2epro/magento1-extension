<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Input */
    protected $_input = null;

    //########################################

    public function setInput(Ess_M2ePro_Model_Listing_Product_Instruction_SynchronizationTemplate_Checker_Input $input)
    {
        $this->_input = $input;
        return $this;
    }

    //########################################

    abstract public function isAllowed();

    abstract public function process(array $params = array());

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product_ScheduledAction_Manager
     */
    protected function getScheduledActionManager()
    {
        return Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');
    }

    protected function setPropertiesForRecheck(array $properties)
    {
        if (empty($properties)) {
            return;
        }

        $additionalData = $this->_input->getListingProduct()->getAdditionalData();

        $existedProperties = array();
        if (!empty($additionalData['recheck_properties'])) {
            $existedProperties = $additionalData['recheck_properties'];
        }

        $properties = array_unique(array_merge($existedProperties, $properties));

        $additionalData['recheck_properties'] = $properties;
        $this->_input->getListingProduct()->setSettings('additional_data', $additionalData)->save();
    }

    //########################################
}
