<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_MigrationNewAmazon extends Ess_M2ePro_Model_Wizard
{
    protected $_steps = array(
        'marketplacesSynchronization',
        'descriptionTemplates',
        'information'
    );

    //########################################

    /**
     * @return array
     */
    public function getSteps()
    {
        $steps = $this->_steps;
        $descriptionTemplatesData = $this->getDataForDescriptionTemplatesStep();

        if (empty($descriptionTemplatesData) &&
            (false !== $index = array_search('descriptionTemplates', $steps))) {
            unset($steps[$index]);
            $steps = array_values($steps);
        }

        return $steps;
    }

    //########################################

    /**
     * @return array
     */
    public function getDataForDescriptionTemplatesStep()
    {
        $tempTemplates = Mage::getModel('M2ePro/Registry')->load('/wizard/new_amazon_description_templates/', 'key')
                                                          ->getData('value');

        return $tempTemplates ? (array)Mage::helper('M2ePro')->jsonDecode($tempTemplates) : array();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return 'migrationNewAmazon';
    }

    //########################################
}
