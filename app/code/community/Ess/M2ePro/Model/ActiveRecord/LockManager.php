<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ActiveRecord_LockManager
{
    /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract */
    protected $_model;

    //########################################

    public function setModel(Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract $model)
    {
        $this->_model = $model;
        return $this;
    }

    //########################################

    public function addProcessingLock($tag = null, $processingId = null)
    {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        if (null === $this->_model->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isSetProcessingLock($tag)) {
            return $this->_model;
        }

        $model = Mage::getModel('M2ePro/Processing_Lock');
        $model->setData(
            array(
                'processing_id' => $processingId,
                'model_name'    => $this->_model->getResourceName(),
                'object_id'     => $this->_model->getId(),
                'tag'           => $tag,
            )
        );
        $model->save();

        return $this->_model;
    }

    public function deleteProcessingLocks($tag = false, $processingId = false)
    {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        if (null === $this->_model->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        foreach ($this->getProcessingLocks($tag, $processingId) as $lock) {
            $lock->deleteInstance();
        }

        return $this->_model;
    }

    public function isSetProcessingLock($tag = false, $processingId = false)
    {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        if (null === $this->_model->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        return count($this->getProcessingLocks($tag, $processingId)) > 0;
    }

    /**
     * @param bool|false $tag
     * @param bool|false $processingId
     *
     * @return Ess_M2ePro_Model_Processing_Lock[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProcessingLocks($tag = false, $processingId = false)
    {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        if (null === $this->_model->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $collection = Mage::getModel('M2ePro/Processing_Lock')->getCollection();
        $collection->addFieldToFilter('model_name', $this->_model->getResourceName());
        $collection->addFieldToFilter('object_id', $this->_model->getId());

        $tag === null && $tag = array('null' => true);
        $tag !== false && $collection->addFieldToFilter('tag', $tag);
        $processingId !== false && $collection->addFieldToFilter('processing_id', $processingId);

        return $collection->getItems();
    }

    //########################################
}
