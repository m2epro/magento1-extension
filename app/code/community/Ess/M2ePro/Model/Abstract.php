<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Abstract extends Mage_Core_Model_Abstract
{
    const SETTING_FIELD_TYPE_JSON          = 'json';
    const SETTING_FIELD_TYPE_SERIALIZATION = 'serialization';

    protected $_isObjectCreatingState = false;

    //########################################

    public function isObjectCreatingState($value = null)
    {
        if ($value === null) {
            return $this->_isObjectCreatingState;
        }

        $this->_isObjectCreatingState = $value;
        return $this->_isObjectCreatingState;
    }

    //########################################

    public function save()
    {
        if ($this->isObjectNew()) {
            $this->isObjectCreatingState(true);
        }

        $result = parent::save();

        $this->isObjectCreatingState(false);
        return $result;
    }

    //########################################

    /**
     * @param int $id
     * @param null|string $field
     * @return Ess_M2ePro_Model_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function loadInstance($id, $field = null)
    {
        $this->load($id, $field);

        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Instance does not exist.',
                array('id'    => $id,
                                                             'field' => $field,
                'model' => $this->_resourceName)
            );
        }

        return $this;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isSetProcessingLock(null)) {
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

    public function delete()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $this->deleteProcessingLocks();
        return parent::delete();
    }

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

    public function addProcessingLock($tag = null, $processingId = null)
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isSetProcessingLock($tag, $processingId)) {
            return;
        }

        $model = Mage::getModel('M2ePro/Processing_Lock');

        $dataForAdd = array(
            'processing_id' => $processingId,
            'model_name'    => $this->_resourceName,
            'object_id'     => $this->getId(),
            'tag'           => $tag,
        );

        $model->setData($dataForAdd)->save();
    }

    public function deleteProcessingLocks($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        foreach ($this->getProcessingLocks($tag, $processingId) as $processingLock) {
            $processingLock->deleteInstance();
        }
    }

    // ---------------------------------------

    public function isSetProcessingLock($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $locks = $this->getProcessingLocks($tag, $processingId);
        return !empty($locks);
    }

    /**
     * @param bool|false $tag
     * @param bool|false $processingId
     * @return Ess_M2ePro_Model_Processing_Lock[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProcessingLocks($tag = false, $processingId = false)
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        $lockedCollection = Mage::getModel('M2ePro/Processing_Lock')->getCollection();

        $lockedCollection->addFieldToFilter('model_name', $this->_resourceName);
        $lockedCollection->addFieldToFilter('object_id', $this->getId());

        $tag === null && $tag = array('null' =>true);
        $tag !== false && $lockedCollection->addFieldToFilter('tag', $tag);
        $processingId !== false && $lockedCollection->addFieldToFilter('processing_id', $processingId);

        return $lockedCollection->getItems();
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

        if ($settings === null) {
            return array();
        }

        if ($encodeType == self::SETTING_FIELD_TYPE_JSON) {
            $settings = Mage::helper('M2ePro')->jsonDecode($settings);
        } else if ($encodeType == self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = Mage::helper('M2ePro')->unserialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                Mage::helper('M2ePro')->__(
                    'Encoding type "%encode_type%" is not supported.',
                    $encodeType
                )
            );
        }

        return !empty($settings) ? $settings : array();
    }

    /**
     * @param string       $fieldName
     * @param string|array $settingNamePath
     * @param mixed        $defaultValue
     * @param string       $encodeType
     *
     * @return mixed|null
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = null,
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
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
            $settings = Mage::helper('M2ePro')->jsonEncode($settings);
        } else if ($encodeType == self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = Mage::helper('M2ePro')->serialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                Mage::helper('M2ePro')->__(
                    'Encoding type "%encode_type%" is not supported.',
                    $encodeType
                )
            );
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
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue,
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
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
}
