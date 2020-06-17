<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract extends Mage_Core_Model_Abstract
{
    //########################################

    /** @var Ess_M2ePro_Model_ActiveRecord_Serializer */
    protected $_serializer;

    /** @var Ess_M2ePro_Model_ActiveRecord_LockManager */
    protected $_lockManager;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    protected $_isCacheEnabled = false;
    protected $_cacheLifetime  = 86400;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_serializer  = Mage::getSingleton('M2ePro/ActiveRecord_Serializer');
        $this->_lockManager = Mage::getSingleton('M2ePro/ActiveRecord_LockManager');

        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    public function getObjectModelName()
    {
        return str_replace('Ess_M2ePro_Model_', '', get_class($this));
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        return $this->getLockManager()->isSetProcessingLock();
    }

    /**
     * @param mixed $tag
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function lock($tag = null)
    {
        !is_array($tag) && $tag = array($tag);
        foreach ($tag as $value) {
            $this->getLockManager()->addProcessingLock($value);
        }

        return $this;
    }

    /**
     * @param mixed $tag
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unlock($tag = false)
    {
        !is_array($tag) && $tag = array($tag);
        foreach ($tag as $value) {
            $this->getLockManager()->deleteProcessingLocks($value);
        }

        return $this;
    }

    //########################################

    /**
     * @param int $id
     * @param null|string $field
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function loadInstance($id, $field = null)
    {
        $this->load($id, $field);

        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Instance does not exist.',
                array(
                    'id'    => $id,
                    'field' => $field,
                    'model' => $this->_resourceName
                )
            );
        }

        return $this;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteInstance()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    //########################################

    //todo active record
    public function deleteProcessings()
    {
        $processingIds = array();
        foreach ($this->getProcessingLocks() as $processingLock) {
            $processingIds[] = $processingLock->getProcessingId();
        }

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Processing')->getCollection();
        $collection->addFieldToFilter('id', array('in'=>array_unique($processingIds)));

        foreach ($collection->getItems() as $processing) {
            /** @var $processing Ess_M2ePro_Model_Processing */

            /** @var Ess_M2ePro_Model_Processing_Runner $processingRunner */
            $processingRunner = Mage::getModel($processing->getModel());
            $processingRunner->setProcessingObject($processing);

            $processingRunner->complete();
        }
    }

    //########################################

    /**
     * @deprecated use $this->getLockManager()->addProcessingLock()
     */
    public function addProcessingLock($tag = null, $processingId = null)
    {
        return $this->getLockManager()->addProcessingLock($tag, $processingId);
    }

    /**
     * @deprecated use $this->getLockManager()->deleteProcessingLocks()
     */
    public function deleteProcessingLocks($tag = false, $processingId = false)
    {
        return $this->getLockManager()->deleteProcessingLocks($tag, $processingId);
    }

    /**
     * @deprecated use $this->getLockManager()->isSetProcessingLock()
     */
    public function isSetProcessingLock($tag = false, $processingId = false)
    {
        return $this->getLockManager()->isSetProcessingLock($tag, $processingId);
    }

    /**
     * @deprecated use $this->getLockManager()->getProcessingLocks()
     */
    public function getProcessingLocks($tag = false, $processingId = false)
    {
        return $this->getLockManager()->getProcessingLocks($tag, $processingId);
    }

    //########################################

    /**
     * @deprecated use $this->getSerializer()->getSettings()
     */
    public function getSettings(
        $fieldName,
        $encodeType = Ess_M2ePro_Model_ActiveRecord_Serializer::SETTING_FIELD_TYPE_JSON
    ) {
        return $this->getSerializer()->getSettings($fieldName, $encodeType);
    }

    /**
     * @deprecated use $this->getSerializer()->getSetting()
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = null,
        $encodeType = Ess_M2ePro_Model_ActiveRecord_Serializer::SETTING_FIELD_TYPE_JSON
    ) {
       return $this->getSerializer()->getSetting($fieldName, $settingNamePath, $defaultValue, $encodeType);
    }

    /**
     * @deprecated use $this->getSerializer()->setSettings()
     */
    public function setSettings(
        $fieldName,
        array $settings = array(),
        $encodeType = Ess_M2ePro_Model_ActiveRecord_Serializer::SETTING_FIELD_TYPE_JSON
    ) {
        $this->getSerializer()->setSettings($fieldName, $settings, $encodeType);
        return $this;
    }

    /**
     * @deprecated use $this->getSerializer()->setSetting()
     */
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue,
        $encodeType = Ess_M2ePro_Model_ActiveRecord_Serializer::SETTING_FIELD_TYPE_JSON
    ) {
        $this->getSerializer()->setSetting($fieldName, $settingNamePath, $settingValue, $encodeType);
        return $this;
    }

    //########################################

    protected function _afterSave()
    {
        if (null !== $this->getId() && $this->isCacheEnabled()) {
            Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues($this->getMainCacheTag());
        }

        return parent::_afterSave();
    }

    protected function _beforeDelete()
    {
        if (null !== $this->getId()) {
            if ($this->isCacheEnabled()) {
                Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues($this->getMainCacheTag());
            }

            $this->unlock();
        }

        return parent::_beforeDelete();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_Serializer
     */
    public function getSerializer()
    {
        return $this->_serializer->setModel($this);
    }

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_LockManager
     */
    public function getLockManager()
    {
        return $this->_lockManager->setModel($this);
    }

    //########################################

    public function isCacheEnabled()
    {
        return $this->_isCacheEnabled;
    }

    public function getCacheLifetime()
    {
        return $this->_cacheLifetime;
    }

    public function getMainCacheTag()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        return strtolower($this->getResourceName() . '_' . $this->getId());
    }

    public function getInstanceCacheTags()
    {
        $modelName = strtolower(str_replace('M2ePro/', '', $this->getResourceName()));

        $tags = array($modelName);
        if (strpos($modelName, '_') !== false) {
            $allComponents = Mage::helper('M2ePro/Component')->getComponents();
            $component     = substr($modelName, 0, strpos($modelName, '_'));

            if (in_array($component, $allComponents)) {
                $tags[] = $component;
                $tags[] = str_replace(ucfirst($component) . '_', '', $modelName);
            }
        }

        return array_unique($tags);
    }

    //########################################
}
