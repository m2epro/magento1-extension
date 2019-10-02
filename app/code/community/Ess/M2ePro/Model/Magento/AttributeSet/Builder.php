<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_AttributeSet_Builder
{
    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    protected $_attributeSetObj = null;

    protected $_setName = null;
    protected $_params  = array();

    protected $_entityTypeId;
    protected $_skeletonId;

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveAttributeSet();
    }

    // ---------------------------------------

    protected function init()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if ($this->_skeletonId !== null) {
            $skeletonAttributeSetId = Mage::getModel('eav/entity_attribute_set')
                  ->load($this->_skeletonId)
                  ->getId();

            !$skeletonAttributeSetId && $this->_skeletonId = null;
        }

        !$this->_skeletonId && Mage::getModel('catalog/product')->getDefaultAttributeSetId();

        $this->_attributeSetObj = Mage::getModel('eav/entity_attribute_set')
                                      ->load($this->_setName, 'attribute_set_name');
    }

    protected function saveAttributeSet()
    {
        if ($this->_attributeSetObj->getId()) {
            return array('result' => true, 'obj' => $this->_attributeSetObj);
        }

        $this->_attributeSetObj->setEntityTypeId($this->_entityTypeId)
                               ->setAttributeSetName($this->_setName);

        try {
            $this->_attributeSetObj->validate();
            $this->_attributeSetObj->save();

            $this->_attributeSetObj->initFromSkeleton($this->_skeletonId)
                                   ->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $this->_attributeSetObj);
    }

    //########################################

    public function setName($value)
    {
        $this->_setName = $value;
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

    public function setSkeletonAttributeSetId($value)
    {
        $this->_skeletonId = $value;
        return $this;
    }

    //########################################
}
