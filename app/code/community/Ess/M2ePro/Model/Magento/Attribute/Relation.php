<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Attribute_Relation
{
    /** @var Mage_Eav_Model_Entity_Attribute */
    private $attributeObj = null;

    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    private $attributeSetObj = null;

    private $code;

    private $setId;
    private $groupName;

    private $entityTypeId;

    private $params = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveRelation();
    }

    // ---------------------------------------

    private function init()
    {
        if (is_null($this->entityTypeId)) {
            $this->entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if (!($this->attributeObj instanceof Mage_Eav_Model_Entity_Attribute)) {

            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($this->entityTypeId, $this->code);
            $attribute->getId() && $this->attributeObj = $attribute;
        }

        if (!($this->attributeSetObj instanceof Mage_Eav_Model_Entity_Attribute_Set)) {

            $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($this->setId);
            $attributeSet->getId() && $this->attributeSetObj = $attributeSet;
        }
    }

    private function saveRelation()
    {
        if (!$this->attributeObj) {
            return array('result' => false, 'error' => "Attribute '{$this->code}' is not found.");
        }

        if (!$this->attributeSetObj) {
            return array('result' => false, 'error' => "Attribute Set '{$this->setId}' is not found.");
        }

        if ($this->checkIsAlreadyInSet()) {
            return array('result' => true);
        }

        $groupId = $this->getGroupId();
        $sortOrder = !empty($this->params['sorder']) ? $this->params['sorder']
                                                     : $this->getMaxSortOrderByGroup($groupId) + 1;

        !empty($this->params['sorder_ofset']) && $sortOrder += $this->params['sorder_ofset'];

        /* @var $collection Mage_Eav_Model_Resource_Entity_Attribute */
        $relation = Mage::getModel('eav/entity_attribute');
        $relation->setEntityTypeId($this->attributeSetObj->getEntityTypeId())
                 ->setAttributeSetId($this->attributeSetObj->getId())
                 ->setAttributeGroupId($groupId)
                 ->setAttributeId($this->attributeObj->getId())
                 ->setSortOrder($sortOrder);

        try {

            $relation->save();

        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $relation);
    }

    //########################################

    private function checkIsAlreadyInSet()
    {
        /* @var $collection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $collection = Mage::getModel('eav/entity_attribute')->getResourceCollection()
              ->setAttributeSetFilter($this->setId)
              ->addFieldToFilter('entity_attribute.attribute_id', $this->attributeObj->getId());

        return $collection->getSize() > 0;
    }

    private function getGroupId()
    {
        if (!$this->groupName) {
            return $this->attributeSetObj->getDefaultGroupId();
        }

        /* @var $collection Mage_Catalog_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('eav/entity_attribute_group')->getCollection();
        $collection->addFieldToFilter('attribute_group_name', $this->groupName);
        $collection->addFieldToFilter('attribute_set_id', $this->setId);

        $firstItem = $collection->getFirstItem();

        if ($firstItem && $firstItem->getId()) {
            return $firstItem->getId();
        }

        return $this->attributeSetObj->getDefaultGroupId();
    }

    private function getMaxSortOrderByGroup($groupId)
    {
        /* @var $collection Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $collection = Mage::getModel('eav/entity_attribute')->getResourceCollection();
        $collection->setAttributeSetFilter($this->setId);
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
        $this->code = $value;
        return $this;
    }

    public function setAttributeSetId($value)
    {
        $this->setId = $value;
        return $this;
    }

    public function setGroupName($value)
    {
        $this->groupName = $value;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->params = $value;
        return $this;
    }

    public function setEntityTypeId($value)
    {
        $this->entityTypeId = $value;
        return $this;
    }

    // ---------------------------------------

    public function setAttributeObj(Mage_Eav_Model_Entity_Attribute $obj)
    {
        $this->attributeObj = $obj;
        $this->code = $obj->getAttributeCode();

        return $this;
    }

    public function setAttributeSetObj(Mage_Eav_Model_Entity_Attribute_Set $obj)
    {
        $this->attributeSetObj = $obj;
        $this->setId = $obj->getId();

        return $this;
    }

    //########################################
}