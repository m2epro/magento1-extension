<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_AttributeSet_Builder
{
    /** @var Mage_Eav_Model_Entity_Attribute_Set */
    private $attributeSetObj  = null;

    private $setName = null;
    private $params = array();

    private $entityTypeId;
    private $skeletonId;

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveAttributeSet();
    }

    // ---------------------------------------

    private function init()
    {
        if (is_null($this->entityTypeId)) {
            $this->entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if (!is_null($this->skeletonId)) {

            $skeletonAttributeSetId = Mage::getModel('eav/entity_attribute_set')
                  ->load($this->skeletonId)
                  ->getId();

            !$skeletonAttributeSetId && $this->skeletonId = null;
        }
        !$this->skeletonId && Mage::getModel('catalog/product')->getDefaultAttributeSetId();

        $this->attributeSetObj = Mage::getModel('eav/entity_attribute_set')
                                       ->load($this->setName, 'attribute_set_name');
    }

    private function saveAttributeSet()
    {
        if ($this->attributeSetObj->getId()) {
            return array('result' => true, 'obj' => $this->attributeSetObj);
        }

        $this->attributeSetObj->setEntityTypeId($this->entityTypeId)
                              ->setAttributeSetName($this->setName);

        try {

            $this->attributeSetObj->validate();
            $this->attributeSetObj->save();

            $this->attributeSetObj->initFromSkeleton($this->skeletonId)
                                  ->save();

        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $this->attributeSetObj);
    }

    //########################################

    public function setName($value)
    {
        $this->setName = $value;
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

    public function setSkeletonAttributeSetId($value)
    {
        $this->skeletonId = $value;
        return $this;
    }

    //########################################
}