<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_LockedObject extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LockedObject');
    }

    //####################################

    public function getModelName()
    {
        return $this->getData('model_name');
    }

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

    //####################################
}