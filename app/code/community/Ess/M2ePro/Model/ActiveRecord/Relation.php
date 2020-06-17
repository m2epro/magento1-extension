<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_ActiveRecord_Relation load($id, $field=null)
 * @method Ess_M2ePro_Model_ActiveRecord_Relation save()
 * @method Ess_M2ePro_Model_Resource_ActiveRecord_Relation getResource()
 * @method Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection getCollection()
 */
class Ess_M2ePro_Model_ActiveRecord_Relation extends Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract */
    protected $_parentObject;

    /** @var Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract */
    protected $_childObject;

    /** @var string */
    protected $_relationKey;

    //########################################

    public function __construct()
    {
        $args = func_get_args();
        list($parentModel, $childModel) = $args[0];

        if (!$parentModel instanceof Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                get_class($parentModel) .
                ' doesn\'t extends Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract'
            );
        }

        if (!$childModel instanceof Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                get_class($childModel) .
                ' doesn\'t extends Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract'
            );
        }

        $this->_parentObject = $parentModel;
        $this->_childObject = $childModel;

        $this->_parentObject->setRelation($this);
        $this->_childObject->setRelation($this);
        parent::__construct();
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ActiveRecord_Relation');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract
     */
    public function getParentObject()
    {
        return $this->_parentObject;
    }

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract
     */
    public function getChildObject()
    {
        return $this->_childObject;
    }

    /**
     * @return string
     */
    public function getRelationKey()
    {
        if (null === $this->_relationKey) {
            $this->_relationKey = str_replace(
                'm2epro_',
                '',
                $this->getParentObject()->getResource()->getMainTable() . '_id'
            );
        }

        return $this->_relationKey;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        return $this->getParentObject()->isLocked() || $this->getChildObject()->isLocked();
    }

    /**
     * @param null $tag
     * @return $this|Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function lock($tag = null)
    {
        $this->getParentObject()->lock($tag);
        $this->getChildObject()->lock($tag);
        return $this;
    }

    /**
     * @param bool $tag
     * @return $this|Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unlock($tag = false)
    {
        $this->getParentObject()->unlock($tag);
        $this->getParentObject()->unlock($tag);
        return $this;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteInstance()
    {
        $this->getParentObject()->deleteInstance();
        $this->getChildObject()->deleteInstance();
        return true;
    }

    //########################################

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getParentObject()->getId();
    }

    /**
     * @param array|string $key
     * @param null $value
     * @return $this|Varien_Object
     */
    public function setData($key, $value = null)
    {
        $this->_hasDataChanges = true;

        if (is_array($key)) {
            $parentData = array();
            $childData = $key;
            foreach ($key as $field => $v) {
                if ($this->getResource()->isModelContainField($this->getParentObject(), $field)) {
                    $parentData[$field] = $v;
                    unset($childData[$field]);
                }
            }

            $this->getParentObject()->setData($parentData);
            $this->getChildObject()->setData($childData);
            return $this;
        }

        if ($this->getResource()->isModelContainField($this->getParentObject(), $key)) {
            $this->getParentObject()->setData($key, $value);
            return $this;
        }

        $this->getChildObject()->setData($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param null $index
     * @return array|mixed
     */
    public function getData($key = '', $index = null)
    {
        if (empty($key)) {
            return array_merge(
                $this->getParentObject()->getData(),
                $this->getChildObject()->getData()
            );
        }

        if ($this->getResource()->isModelContainField($this->getParentObject(), $key)) {
            return $this->getParentObject()->getData($key, $index);
        }

        return $this->getChildObject()->getData($key, $index);
    }

    /**
     * @param null $key
     * @return $this|Varien_Object
     */
    public function unsetData($key = null)
    {
        if (null === $key) {
            $this->getParentObject()->unsetData();
            $this->getChildObject()->unsetData();
            return $this;
        }

        if ($this->getResource()->isModelContainField($this->getParentObject(), $key)) {
            $this->getParentObject()->unsetData($key);
            return $this;
        }

        $this->getChildObject()->unsetData($key);
        return $this;
    }

    /**
     * @param null $key
     * @param null $data
     * @return $this|Varien_Object
     */
    public function setOrigData($key = null, $data = null)
    {
        if (null === $key) {
            $this->getParentObject()->setOrigData();
            $this->getChildObject()->setOrigData();
            return $this;
        }

        if ($this->getResource()->isModelContainField($this->getParentObject(), $key)) {
            $this->getParentObject()->setOrigData($key, $data);
            return $this;
        }

        $this->getChildObject()->setOrigData($key, $data);
        return $this;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getOrigData($key = null)
    {
        if (null === $key) {
            return array_merge(
                $this->getParentObject()->getOrigData(),
                $this->getChildObject()->getOrigData()
            );
        }

        if ($this->getResource()->isModelContainField($this->getParentObject(), $key)) {
            return $this->getParentObject()->getOrigData($key);
        }

        return $this->getChildObject()->getOrigData($key);
    }

    public function toArray(array $arrAttributes = array())
    {
        $this->_data = $this->getData();
        return parent::toArray($arrAttributes);
    }

    //########################################

    protected function _getResource()
    {
        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        return Mage::getResourceSingleton($this->_resourceName, array($this));
    }

    /**
     * @return Object|string
     * @throws Mage_Core_Exception
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollectionName)) {
            Mage::throwException(Mage::helper('core')->__('Model collection resource name is not defined.'));
        }

        return Mage::getResourceModel($this->_resourceCollectionName, array($this->_getResource(), $this));
    }

    //########################################
}
