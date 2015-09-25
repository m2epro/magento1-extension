<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Buy_Synchronization_Templates_Abstract
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    protected $runner = NULL;

    /**
     * @var Ess_M2ePro_Model_Buy_Synchronization_Templates_Inspector
     */
    protected $inspector = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Changes
     */
    protected $changesHelper = NULL;

    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::TEMPLATES;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Templates_'.$taskPath);
    }

    // -----------------------------------

    public function setRunner(Ess_M2ePro_Model_Synchronization_Templates_Runner $object)
    {
        $this->runner = $object;
    }

    public function getRunner()
    {
        return $this->runner;
    }

    // -----------------------------------

    public function setInspector(Ess_M2ePro_Model_Buy_Synchronization_Templates_Inspector $object)
    {
        $this->inspector = $object;
    }

    public function getInspector()
    {
        return $this->inspector;
    }

    // -----------------------------------

    public function setChangesHelper(Ess_M2ePro_Model_Synchronization_Templates_Changes $object)
    {
        $this->changesHelper = $object;
    }

    public function getChangesHelper()
    {
        return $this->changesHelper;
    }

    //####################################
}