<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Attribute_Builder as AttributeBuilder;

class Ess_M2ePro_Block_Adminhtml_General_CreateAttribute extends Mage_Adminhtml_Block_Widget
{
    protected $_handlerId;

    protected $_allowedTypes            = array();
    protected $_applyToAllAttributeSets = true;

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
        if ($value === null) {
            return $this->_handlerId;
        }

        $this->_handlerId = $value;
        return $this->_handlerId;
    }

    public function applyToAll($value = null)
    {
        if ($value === null) {
            return $this->_applyToAllAttributeSets;
        }

        $this->_applyToAllAttributeSets = $value;
        return $this->_applyToAllAttributeSets;
    }

    public function allowedTypes($value = null)
    {
        if ($value === null) {
            return count($this->_allowedTypes) ? $this->_allowedTypes : $this->getAllAvailableTypes();
        }

        $this->_allowedTypes = $value;
        return $this->_allowedTypes;
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
