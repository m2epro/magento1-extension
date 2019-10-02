<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Attribute_Relation
{
    /** @var Mage_Eav_Model_Entity_Attribute */
    protected $_attributeObj = null;

    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    protected $_attributeSetObj = null;

    protected $_code;

    protected $_setId;
    protected $_groupName;

    protected $_entityTypeId;

    protected $_params = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveRelation();
    }

    // ---------------------------------------

    protected function init()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if (!($this->_attributeObj instanceof Mage_Eav_Model_Entity_Attribute)) {
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($this->_entityTypeId, $this->_code);
            $attribute->getId() && $this->_attributeObj = $attribute;
        }

        if (!($this->_attributeSetObj instanceof Mage_Eav_Model_Entity_Attribute_Set)) {
            $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($this->_setId);
            $attributeSet->getId() && $this->_attributeSetObj = $attributeSet;
        }
    }

    protected function saveRelation()
    {
        if (!$this->_attributeObj) {
            return array('result' => false, 'error' => "Attribute '{$this->_code}' is not found.");
        }

        if (!$this->_attributeSetObj) {
            return array('result' => false, 'error' => "Attribute Set '{$this->_setId}' is not found.");
        }

        if ($this->checkIsAlreadyInSet()) {
            return array('result' => true);
        }

        $groupId = $this->getGroupId();
        $sortOrder = !empty($this->_params['sorder']) ? $this->_params['sorder']
                                                     : $this->getMaxSortOrderByGroup($groupId) + 1;

        !empty($this->_params['sorder_ofset']) && $sortOrder += $this->_params['sorder_ofset'];

        /** @var $collection Mage_Eav_Model_Resource_Entity_Attribute */
        $relation = Mage::getModel('eav/entity_attribute');
        $relation->setEntityTypeId($this->_attributeSetObj->getEntityTypeId())
                 ->setAttributeSetId($this->_attributeSetObj->getId())
                 ->setAttributeGroupId($groupId)
                 ->setAttributeId($this->_attributeObj->getId())
                 ->setSortOrder($sortOrder);

        try {
            $relation->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $relation);
    }

    //########################################

    protected function checkIsAlreadyInSet()
    {
        /** @var $collection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $collection = Mage::getModel('eav/entity_attribute')->getResourceCollection()
              ->setAttributeSetFilter($this->_setId)
              ->addFieldToFilter('entity_attribute.attribute_id', $this->_attributeObj->getId());

        return $collection->getSize() > 0;
    }

    protected function getGroupId()
    {
        if (!$this->_groupName) {
            return $this->_attributeSetObj->getDefaultGroupId();
        }

        /** @var $collection Mage_Catalog_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('eav/entity_attribute_group')->getCollection();
        $collection->addFieldToFilter('attribute_group_name', $this->_groupName);
        $collection->addFieldToFilter('attribute_set_id', $this->_setId);

        $firstItem = $collection->getFirstItem();

        if ($firstItem && $firstItem->getId()) {
            return $firstItem->getId();
        }

        return $this->_attributeSetObj->getDefaultGroupId();
    }

    protected function getMaxSortOrderByGroup($groupId)
    {
        /** @var $collection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $collection = Mage::getModel('eav/entity_attribute')->getResourceCollection();
        $collection->setAttributeSetFilter($this->_setId);
        $collection->setAttributeGroupFilter($groupId);
        $collection->setOrder('sort_order', 'DESC');

        if ($firstItem = $collection->getFirstItem()) {
            return (int)$firstItem->getData('sort_order');
        }

        return 0;
    }

    //########################################

    public function setCode($value)
    {
        $this->_code = $value;
        return $this;
    }

    public function setAttributeSetId($value)
    {
        $this->_setId = $value;
        return $this;
    }

    public function setGroupName($value)
    {
        $this->_groupName = $value;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->_params = $value;
        return $this;
    }

    public function setEntityTypeId($value)
    {
        $this->_entityTypeId = $value;
        return $this;
    }

    // ---------------------------------------

    public function setAttributeObj(Mage_Eav_Model_Entity_Attribute $obj)
    {
        $this->_attributeObj = $obj;
        $this->_code         = $obj->getAttributeCode();

        return $this;
    }

    public function setAttributeSetObj(Mage_Eav_Model_Entity_Attribute_Set $obj)
    {
        $this->_attributeSetObj = $obj;
        $this->_setId           = $obj->getId();

        return $this;
    }

    //########################################
}
