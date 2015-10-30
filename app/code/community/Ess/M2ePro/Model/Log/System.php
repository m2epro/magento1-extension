<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Log_System extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Log_System');
    }

    //########################################

    public function setType($type)
    {
        $this->setData('type', $type);
    }

    public function getType()
    {
        return $this->getData('type');
    }

    // ---------------------------------------

    public function setDescription($description)
    {
        $this->setData('description', $description);
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    // ---------------------------------------

    /**
     * @param array $data
     */
    public function setAdditionalData(array $data = array())
    {
        $this->setData('additional_data', json_encode($data));
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return (array)json_decode($this->getData('additional_data'), true);
    }

    //########################################
}