<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ArchivedEntity extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ArchivedEntity');
    }

    //########################################

    public function deleteProcessingLocks($tag = false, $processingId = false) {}

    //########################################

    public function getName()
    {
        return $this->getData('name');
    }

    public function getOriginId()
    {
        return $this->getData('origin_id');
    }

    public function getOriginData()
    {
        return $this->getData('data');
    }

    //########################################
}