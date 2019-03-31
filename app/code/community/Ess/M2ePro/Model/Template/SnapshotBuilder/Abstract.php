<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    /** @var Ess_M2ePro_Model_Abstract */
    protected $model = NULL;

    //########################################

    public function setModel(Ess_M2ePro_Model_Abstract $model)
    {
        $this->model = $model;
        return $this;
    }

    //########################################

    abstract public function getSnapshot();

    //########################################
}