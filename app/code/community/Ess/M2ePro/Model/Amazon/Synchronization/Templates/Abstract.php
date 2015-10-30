<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    protected $runner = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector
     */
    protected $inspector = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Changes
     */
    protected $changesHelper = NULL;

    //########################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::TEMPLATES;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Templates_'.$taskPath);
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Synchronization_Templates_Runner $object
     */
    public function setRunner(Ess_M2ePro_Model_Synchronization_Templates_Runner $object)
    {
        $this->runner = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector $object
     */
    public function setInspector(Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector $object)
    {
        $this->inspector = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Synchronization_Templates_Changes $object
     */
    public function setChangesHelper(Ess_M2ePro_Model_Synchronization_Templates_Changes $object)
    {
        $this->changesHelper = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Templates_Changes
     */
    public function getChangesHelper()
    {
        return $this->changesHelper;
    }

    //########################################
}