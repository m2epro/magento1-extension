<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Log_System extends Ess_M2ePro_Model_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Log_System');
    }

    // ########################################

    public function setType($type)
    {
        $this->setData('type', $type);
    }

    public function getType()
    {
        return $this->getData('type');
    }

    // ----------------------------------------

    public function setDescription($description)
    {
        $this->setData('description', $description);
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    // ----------------------------------------

    public function setAdditionalData(array $data = array())
    {
        $this->setData('additional_data', json_encode($data));
    }

    public function getAdditionalData()
    {
        return (array)json_decode($this->getData('additional_data'), true);
    }

    // ########################################
}