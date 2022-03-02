<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Repository
{
    /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Definition[] */
    private $definitions;

    public function __construct()
    {
        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Repository_DefinitionProvider $definitionProvider */
        $definitionProvider = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Repository_DefinitionProvider');

        foreach ($definitionProvider->getDefinitions() as $definition) {
            $this->definitions[$definition->getNick()] = $definition;
        }
    }

    /**
     * @param string $nick
     *
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Definition
     */
    public function getDefinition($nick)
    {
        return $this->definitions[$nick];
    }

    /**
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Definition[]
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }
}
