<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Setup getResource()
 */
class Ess_M2ePro_Model_Setup extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Setup');
    }

    //####################################

    public function getVersionFrom()
    {
        return $this->getData('version_from');
    }

    public function getVersionTo()
    {
        return $this->getData('version_to');
    }

    public function isBackuped()
    {
        return (bool)$this->getData('is_backuped');
    }

    public function isCompleted()
    {
        return (bool)$this->getData('is_completed');
    }

    public function getProfilerData()
    {
        return (array)$this->getSettings('profiler_data');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    //####################################
}