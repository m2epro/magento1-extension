<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_LockedObject extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LockedObject');
    }

    //########################################

    public function getModelName()
    {
        return $this->getData('model_name');
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return (int)$this->getData('object_id');
    }

    public function getRelatedHash()
    {
        return $this->getData('related_hash');
    }

    public function getTag()
    {
        return $this->getData('tag');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    //########################################
}