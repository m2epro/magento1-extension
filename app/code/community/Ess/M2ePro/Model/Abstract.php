<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Abstract extends Mage_Core_Model_Abstract
{
    const SETTING_FIELD_TYPE_JSON          = 'json';
    const SETTING_FIELD_TYPE_SERIALIZATION = 'serialization';

    //########################################

    /**
     * @param int $id
     * @param null|string $field
     * @return Ess_M2ePro_Model_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function loadInstance($id, $field = NULL)
    {
        $this->load($id,$field);

        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance does not exist.',
                                                       array('id'    => $id,
                                                             'field' => $field,
                                                             'model' => $this->_resourceName));
        }

        return $this;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLockedObject(NULL)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteInstance()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    //########################################

    public function delete()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $this->deleteObjectLocks();
        return parent::delete();
    }

    public function deleteProcessingRequests()
    {
        $processingRequestsHashes = array();
        foreach ($this->getObjectLocks() as $lockedObject) {
            $processingRequestsHashes[] = $lockedObject->getRelatedHash();
        }

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Processing_Request')->getCollection();
        $collection->addFieldToFilter('hash', array('in'=>array_unique($processingRequestsHashes)));

        foreach ($collection->getItems() as $processingRequest) {
            /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
            //->__('Request was deleted during object deleting.')
            $processingRequest->getResponserRunner()->complete('Request was deleted during object deleting.');
        }
    }

    //########################################

    public function addObjectLock($tag = NULL, $relatedHash = NULL, $description = NULL)
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLockedObject($tag,$relatedHash)) {
            return;
        }

        $model = Mage::getModel('M2ePro/LockedObject');

        $dataForAdd = array(
            'model_name' => $this->_resourceName,
            'object_id' => $this->getId(),
            'related_hash' => $relatedHash,
            'tag' => $tag,
            'description' => $description
        );

        $model->setData($dataForAdd)->save();
    }

    public function deleteObjectLocks($tag = false, $relatedHash = false)
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $lockedObjects = $this->getObjectLocks($tag,$relatedHash);
        foreach ($lockedObjects as $lockedObject) {
            /** @var $lockedObject Ess_M2ePro_Model_LockedObject */
            $lockedObject->deleteInstance();
        }
    }

    // ---------------------------------------

    public function isLockedObject($tag = false, $relatedHash = false)
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        return count($this->getObjectLocks($tag,$relatedHash)) > 0;
    }

    public function getObjectLocks($tag = false, $relatedHash = false)
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $lockedCollection = Mage::getModel('M2ePro/LockedObject')->getCollection();

        $lockedCollection->addFieldToFilter('model_name',$this->_resourceName);
        $lockedCollection->addFieldToFilter('object_id',$this->getId());

        is_null($tag) && $tag = array('null'=>true);
        $tag !== false && $lockedCollection->addFieldToFilter('tag',$tag);
        $relatedHash !== false && $lockedCollection->addFieldToFilter('related_hash',$relatedHash);

        return $lockedCollection->getItems();
    }

    //########################################

    /**
     * @param string $modelName
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelatedSimpleItems($modelName, $fieldName, $asObjects = false,
                                             array $filters = array(), array $sort = array())
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $tempModel = Mage::getModel('M2ePro/'.$modelName);

        if (is_null($tempModel) || !($tempModel instanceof Ess_M2ePro_Model_Abstract)) {
            return array();
        }

        return $this->getRelatedItems($tempModel,$fieldName,$asObjects,$filters,$sort);
    }

    /**
     * @param Ess_M2ePro_Model_Abstract $model
     * @param string $fieldName
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getRelatedItems(Ess_M2ePro_Model_Abstract $model, $fieldName, $asObjects = false,
                                       array $filters = array(), array $sort = array())
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        /** @var $tempCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $tempCollection = $model->getCollection();
        $tempCollection->addFieldToFilter(new Zend_Db_Expr("`{$fieldName}`"), $this->getId());

        foreach ($filters as $field=>$filter) {

            if ($filter instanceof Zend_Db_Expr) {
                $tempCollection->getSelect()->where((string)$filter);
                continue;
            }

            $tempCollection->addFieldToFilter(new Zend_Db_Expr("`{$field}`"), $filter);
        }

        foreach ($sort as $field => $order) {
            $order = strtoupper(trim($order));
            if ($order != Varien_Data_Collection::SORT_ORDER_ASC &&
                $order != Varien_Data_Collection::SORT_ORDER_DESC) {
                continue;
            }
            $tempCollection->setOrder($field,$order);
        }

        if ((bool)$asObjects) {
            return $tempCollection->getItems();
        }

        $tempArray = $tempCollection->toArray();
        return $tempArray['items'];
    }

    //########################################

    /**
     * @param string $fieldName
     * @param string $encodeType
     *
     * @return array
     *
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSettings($fieldName, $encodeType = self::SETTING_FIELD_TYPE_JSON)
    {
        $settings = $this->getData((string)$fieldName);

        if (is_null($settings)) {
            return array();
        }

        if ($encodeType == self::SETTING_FIELD_TYPE_JSON) {
            $settings = @json_decode($settings, true);
        } else if ($encodeType == self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = @unserialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(Mage::helper('M2ePro')->__(
                'Encoding type "%encode_type%" is not supported.',
                $encodeType
                ));
        }

        return is_array($settings) ? $settings : array();
    }

    /**
     * @param string       $fieldName
     * @param string|array $settingNamePath
     * @param mixed        $defaultValue
     * @param string       $encodeType
     *
     * @return mixed|null
     */
    public function getSetting($fieldName,
                               $settingNamePath,
                               $defaultValue = NULL,
                               $encodeType = self::SETTING_FIELD_TYPE_JSON)
    {
        if (empty($settingNamePath)) {
            return $defaultValue;
        }

        $settings = $this->getSettings($fieldName, $encodeType);

        !is_array($settingNamePath) && $settingNamePath = array($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            if (!isset($settings[$pathPart])) {
                return $defaultValue;
            }

            $settings = $settings[$pathPart];
        }

        if (is_numeric($settings)) {
            $settings = ctype_digit($settings) ? (int)$settings : $settings;
        }

        return $settings;
    }

    // ---------------------------------------

    /**
     * @param string $fieldName
     * @param array  $settings
     * @param string $encodeType
     *
     * @return Ess_M2ePro_Model_Abstract
     *
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setSettings($fieldName, array $settings = array(), $encodeType = self::SETTING_FIELD_TYPE_JSON)
    {
        if ($encodeType == self::SETTING_FIELD_TYPE_JSON) {
            $settings = json_encode($settings);
        } else if ($encodeType == self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = serialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(Mage::helper('M2ePro')->__(
                'Encoding type "%encode_type%" is not supported.',
                $encodeType
            ));
        }

        $this->setData((string)$fieldName, $settings);

        return $this;
    }

    /**
     * @param string       $fieldName
     * @param string|array $settingNamePath
     * @param mixed        $settingValue
     * @param string       $encodeType
     *
     * @return Ess_M2ePro_Model_Abstract
     */
    public function setSetting($fieldName,
                               $settingNamePath,
                               $settingValue,
                               $encodeType = self::SETTING_FIELD_TYPE_JSON)
    {
        if (empty($settingNamePath)) {
            return $this;
        }

        $settings = $this->getSettings($fieldName, $encodeType);
        $target = &$settings;

        !is_array($settingNamePath) && $settingNamePath = array($settingNamePath);

        $currentPathNumber = 0;
        $totalPartsNumber = count($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            $currentPathNumber++;

            if (!array_key_exists($pathPart, $settings) && $currentPathNumber != $totalPartsNumber) {
                $target[$pathPart] = array();
            }

            if ($currentPathNumber != $totalPartsNumber) {
                $target = &$target[$pathPart];
                continue;
            }

            $target[$pathPart] = $settingValue;
        }

        $this->setSettings($fieldName, $settings, $encodeType);

        return $this;
    }

    //########################################

    public function getDataSnapshot()
    {
        $data = $this->getData();

        foreach ($data as &$value) {
            !is_null($value) && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    //########################################
}