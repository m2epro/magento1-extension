<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Resource_ActiveRecord_ActiveRecordAbstract
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Use is object new method for save of object
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory  */
    protected $_activeRecordFactory;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract $object */

        if (null === $object->getId()) {
            $object->setData('create_date', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $object->setData('update_date', Mage::helper('M2ePro')->getCurrentGmtDate());

        $result = parent::_beforeSave($object);

        // fix for Varien_Db_Adapter_Pdo_Mysql::prepareColumnValue
        // an empty string cannot be saved -> NULL is saved instead
        // for Magento version > 1.6.x.x
        foreach ($object->getData() as $key => $value) {
            $value === '' && $object->setData($key, new Zend_Db_Expr("''"));
        }

        return $result;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract $object */

        // fix for Varien_Db_Adapter_Pdo_Mysql::prepareColumnValue
        // an empty string cannot be saved -> NULL is saved instead
        // for Magento version > 1.6.x.x
        foreach ($object->getData() as $key => $value) {
            if ($value instanceof Zend_Db_Expr && $value->__toString() === '\'\'') {
                $object->setData($key, '');
            }
        }

        return parent::_afterSave($object);
    }

    //########################################
}
