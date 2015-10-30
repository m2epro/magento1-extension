<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Attribute_Builder as AttributeBuilder;

class Ess_M2ePro_Block_Adminhtml_General_CreateAttribute extends Mage_Adminhtml_Block_Widget
{
    protected $handlerId;

    protected $allowedTypes = array();
    protected $applyToAllAttributeSets = true;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('generalCreateAttribute');
        // ---------------------------------------

        $this->setTemplate('M2ePro/general/createAttribute.phtml');
    }

    //########################################

    public function handlerId($value = null)
    {
        if (is_null($value)) {
            return $this->handlerId;
        }

        $this->handlerId = $value;
        return $this->handlerId;
    }

    public function applyToAll($value = null)
    {
        if (is_null($value)) {
            return $this->applyToAllAttributeSets;
        }

        $this->applyToAllAttributeSets = $value;
        return $this->applyToAllAttributeSets;
    }

    public function allowedTypes($value = null)
    {
        if (is_null($value)) {
            return count($this->allowedTypes) ? $this->allowedTypes : $this->getAllAvailableTypes();
        }

        $this->allowedTypes = $value;
        return $this->allowedTypes;
    }

    // ---------------------------------------

    public function getTitleByType($type)
    {
        $titles =  array(
            AttributeBuilder::TYPE_TEXT            => Mage::helper('M2ePro')->__('Text Field'),
            AttributeBuilder::TYPE_TEXTAREA        => Mage::helper('M2ePro')->__('Text Area'),
            AttributeBuilder::TYPE_PRICE           => Mage::helper('M2ePro')->__('Price'),
            AttributeBuilder::TYPE_SELECT          => Mage::helper('M2ePro')->__('Select'),
            AttributeBuilder::TYPE_MULTIPLE_SELECT => Mage::helper('M2ePro')->__('Multiple Select'),
            AttributeBuilder::TYPE_DATE            => Mage::helper('M2ePro')->__('Date'),
            AttributeBuilder::TYPE_BOOLEAN         => Mage::helper('M2ePro')->__('Yes/No')
        );

        return isset($titles[$type]) ? $titles[$type] : Mage::helper('M2ePro')->__('N/A');
    }

    public function getAllAvailableTypes()
    {
        return array(
            AttributeBuilder::TYPE_TEXT,
            AttributeBuilder::TYPE_TEXTAREA,
            AttributeBuilder::TYPE_PRICE,
            AttributeBuilder::TYPE_SELECT,
            AttributeBuilder::TYPE_MULTIPLE_SELECT,
            AttributeBuilder::TYPE_DATE,
            AttributeBuilder::TYPE_BOOLEAN
        );
    }

    public function isOneOnlyTypeAllowed()
    {
        return count($this->allowedTypes()) == 1;
    }

    //########################################
}