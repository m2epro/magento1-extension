<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Customer extends Mage_Core_Model_Abstract
{
    const FAKE_EMAIL_POSTFIX = '@dummy.email';

    /** @var $_customer Mage_Customer_Model_Customer */
    protected $_customer = null;

    //########################################

    public function getCustomer()
    {
        return $this->_customer;
    }

    //########################################

    public function buildCustomer()
    {
        $password = Mage::helper('core')->getRandomString(7);

        /**
         * Magento can replace customer group to the default.
         * app/code/core/Mage/Sales/Model/Observer.php:430
         * Can be disabled here:
         * Customers -> Customer Configuration -> Create new account options -> Automatic Assignment to Customer Group
         */
        $this->_customer = Mage::getModel('customer/customer')
            ->setData('prefix', $this->getData('customer_prefix'))
            ->setData('firstname', $this->getData('customer_firstname'))
            ->setData('middlename', $this->getData('customer_middlename'))
            ->setData('lastname', $this->getData('customer_lastname'))
            ->setData('suffix', $this->getData('customer_suffix'))
            ->setData('website_id', $this->getData('website_id'))
            ->setData('group_id', $this->getData('group_id'))
            ->setData('email', $this->getData('email'));

        if ($this->_customer->isConfirmationRequired()) {
            $this->_customer->setData('confirmation', $password);
        } else {
            $this->_customer->setForceConfirmed(true);
        }

        $this->_customer->setPassword($password);
        $this->_customer->save();

        $this->_customer->setOrigData();

        // Add customer address
        // do not replace setCustomerId with setData('customer_id', ..)
        $customerAddress = Mage::getModel('customer/address')
            ->setData('prefix', $this->getData('prefix'))
            ->setData('firstname', $this->getData('firstname'))
            ->setData('middlename', $this->getData('middlename'))
            ->setData('lastname', $this->getData('lastname'))
            ->setData('suffix', $this->getData('suffix'))
            ->setData('country_id', $this->getData('country_id'))
            ->setData('region', $this->getData('region'))
            ->setData('region_id', $this->getData('region_id'))
            ->setData('city', $this->getData('city'))
            ->setData('postcode', $this->getData('postcode'))
            ->setData('telephone', $this->getData('telephone'))
            ->setData('street', $this->getData('street'))
            ->setData('company', $this->getData('company'))
            ->setCustomerId($this->_customer->getId())
            ->setIsDefaultBilling(true)
            ->setIsDefaultShipping(true);

        $customerAddress->implodeStreetAddress();
        $customerAddress->save();
        // ---------------------------------------
    }

    //########################################

    public function buildAttribute($code, $label)
    {
        try {
            $attributeBuilder = Mage::getModel('M2ePro/Magento_Attribute_Builder');
            $attributeBuilder->setCode($code);
            $attributeBuilder->setLabel($label);
            $attributeBuilder->setInputType('text');
            $attributeBuilder->setEntityTypeId(Mage::getModel('customer/customer')->getEntityTypeId());

            $result = $attributeBuilder->save();
            if (!$result['result']) {
                return;
            }

            /** @var Mage_Eav_Model_Entity_Attribute $attribute */
            $attribute = $result['obj'];

            $defaultAttributeSetId = $this->getDefaultAttributeSetId();

            $this->addAttributeToGroup(
                $attribute->getId(), $defaultAttributeSetId, $this->getDefaultAttributeGroupId($defaultAttributeSetId)
            );
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ---------------------------------------

    protected function addAttributeToGroup($attributeId, $attributeSetId, $attributeGroupId)
    {
        $resource = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $data = array(
            'entity_type_id'      => Mage::getModel('customer/customer')->getEntityTypeId(),
            'attribute_set_id'    => $attributeSetId,
            'attribute_group_id'  => $attributeGroupId,
            'attribute_id'        => $attributeId,
        );

        $connWrite->insert(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('eav/entity_attribute'),
            $data
        );
    }

    protected function getDefaultAttributeSetId()
    {
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('eav/entity_type'),
                'default_attribute_set_id'
            )
            ->where('entity_type_id = ?', Mage::getModel('customer/customer')->getEntityTypeId());

        return $connRead->fetchOne($select);
    }

    protected function getDefaultAttributeGroupId($attributeSetId)
    {
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('eav/attribute_group'),
                'attribute_group_id'
            )
            ->where('attribute_set_id = ?', $attributeSetId)
            ->order(array('default_id ' . Varien_Db_Select::SQL_DESC, 'sort_order'))
            ->limit(1);

        return $connRead->fetchOne($select);
    }

    //########################################
}
