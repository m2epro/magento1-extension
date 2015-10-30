<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_AttributeSet_Group
{
    /** @var Mage_Eav_Model_Entity_Attribute_Group */
    private $groupObj = null;

    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    private $attributeSetObj = null;

    private $name;
    private $attributeSetId;

    private $params = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveGroup();
    }

    private function init()
    {
        if (!($this->attributeSetObj instanceof Mage_Eav_Model_Entity_Attribute_Set)) {

            $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($this->attributeSetId);
            $attributeSet->getId() && $this->attributeSetObj = $attributeSet;
        }

        $tempCollection = Mage::getModel('eav/entity_attribute_group')->getCollection()
              ->addFieldToFilter('attribute_group_name', $this->name)
              ->addFieldToFilter('attribute_set_id', $this->attributeSetId);

        $tempCollection->getSelect()->limit(1);
        $this->groupObj = $tempCollection->getFirstItem();
    }

    // ---------------------------------------

    private function saveGroup()
    {
        if ($this->groupObj->getId()) {
            return array('result' => true);
        }

        if (!$this->attributeSetObj) {
            return array('result' => false, 'error' => "Attribute Set '{$this->attributeSetId}' is not found.");
        }

        $this->groupObj->setAttributeGroupName($this->name);
        $this->groupObj->setAttributeSetId($this->attributeSetId);

        try {
            $this->groupObj->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $this->groupObj);
    }

    //########################################

    public function setGroupName($value)
    {
        $this->name = $value;
        return $this;
    }

    public function setAttributeSetId($value)
    {
        $this->attributeSetId = $value;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->params = $value;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Set $obj
     * @return $this
     */
    public function setAttributeSetObj(Mage_Eav_Model_Entity_Attribute_Set $obj)
    {
        $this->attributeSetObj = $obj;
        $this->attributeSetId = $obj->getId();

        return $this;
    }

    //########################################
}