<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Template_AffectedListingsProducts_Abstract
{
    /** @var Ess_M2ePro_Model_Abstract */
    protected $_model = null;

    //########################################

    public function setModel(Ess_M2ePro_Model_Abstract $model)
    {
        $this->_model = $model;
        return $this;
    }

    //########################################

    abstract public function getObjects(array $filters = array());

    abstract public function getData($columns = '*', array $filters = array());

    abstract public function getIds(array $filters = array());

    //########################################
}
