<?php

class Ess_M2ePro_Model_ActiveRecord_Factory
{
    //########################################

    /**
     * @param string $modelName
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObject($modelName)
    {
        $model = Mage::getModel('M2ePro/' . $modelName);

        if (!$model instanceof Ess_M2ePro_Model_Abstract &&
            !$model instanceof Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
        ) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                sprintf(
                    '%s doesn\'t extends Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract', $modelName
                )
            );
        }

        return $model;
    }

    /**
     * @param string $modelName
     *
     * @return Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract|Ess_M2ePro_Model_Resource_Collection_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectCollection($modelName)
    {
        return $this->getObject($modelName)->getCollection();
    }

    /**
     * @param string $modelName
     * @param mixed $value
     * @param null|string $field
     * @param boolean $throwException
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        try {
            return $this->getObject($modelName)->loadInstance($value, $field);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            if ($throwException) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @param mixed $modelName
     * @param mixed $value
     * @param null|string $field
     * @param boolean $throwException
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCachedObjectLoaded($modelName, $value, $field = null, $throwException = true)
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return $this->getObjectLoaded($modelName, $value, $field, $throwException);
        }

        $model = $this->getObject($modelName);

        if (!$model->isCacheEnabled()) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('%s can\'t be cached', $modelName));
        }

        $cacheKey = strtoupper($modelName.'_data_'.$field.'_'.$value);

        $cacheData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);
        if (!empty($cacheData) && is_array($cacheData)) {
            $model->setData($cacheData);
            $model->setOrigData();

            return $model;
        }

        try {
            $model->loadInstance($value, $field);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            if ($throwException) {
                throw $e;
            }

            return null;
        }

        $tags   = $model->getInstanceCacheTags();
        $tags[] = $model->getMainCacheTag();

        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
            $cacheKey,
            $model->getData(),
            $tags,
            $model->getCacheLifetime()
        );

        return $model;
    }

    //########################################
}
