<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_AttributeSet_Group
{
    /** @var Mage_Eav_Model_Entity_Attribute_Group */
    protected $_groupObj = null;

    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    protected $_attributeSetObj = null;

    protected $_name;
    protected $_attributeSetId;

    protected $_params = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveGroup();
    }

    protected function init()
    {
        if (!($this->_attributeSetObj instanceof Mage_Eav_Model_Entity_Attribute_Set)) {
            $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($this->_attributeSetId);
            $attributeSet->getId() && $this->_attributeSetObj = $attributeSet;
        }

        $tempCollection = Mage::getModel('eav/entity_attribute_group')->getCollection()
              ->addFieldToFilter('attribute_group_name', $this->_name)
              ->addFieldToFilter('attribute_set_id', $this->_attributeSetId);

        $tempCollection->getSelect()->limit(1);
        $this->_groupObj = $tempCollection->getFirstItem();
    }

    // ---------------------------------------

    protected function saveGroup()
    {
        if ($this->_groupObj->getId()) {
            return array('result' => true);
        }

        if (!$this->_attributeSetObj) {
            return array('result' => false, 'error' => "Attribute Set '{$this->_attributeSetId}' is not found.");
        }

        $this->_groupObj->setAttributeGroupName($this->_name);
        $this->_groupObj->setAttributeSetId($this->_attributeSetId);

        try {
            $this->_groupObj->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $this->_groupObj);
    }

    //########################################

    public function setGroupName($value)
    {
        $this->_name = $value;
        return $this;
    }

    public function setAttributeSetId($value)
    {
        $this->_attributeSetId = $value;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->_params = $value;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param Mage_Eav_Model_Entity_Attribute_Set $obj
     * @return $this
     */
    public function setAttributeSetObj(Mage_Eav_Model_Entity_Attribute_Set $obj)
    {
        $this->_attributeSetObj = $obj;
        $this->_attributeSetId  = $obj->getId();

        return $this;
    }

    //########################################
}
