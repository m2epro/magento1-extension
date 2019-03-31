<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Processing_Lock extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Processing_Lock');
    }

    //####################################

    public function getProcessingId()
    {
        return (int)$this->getData('processing_id');
    }

    public function getModelName()
    {
        return $this->getData('model_name');
    }

    public function getObjectId()
    {
        return (int)$this->getData('object_id');
    }

    public function getTag()
    {
        return $this->getData('tag');
    }

    //####################################

    /**
     * This object can NOT be locked. So we are avoiding unnecessary queries to the database.
     * @return bool
     */
    public function isLocked()
    {
        return false;
    }

    public function deleteProcessingLocks($tag = false, $processingId = false) {}

    //####################################
}