<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Abstract
    extends Mage_Core_Model_Mysql4_Abstract
{
    // ########################################

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $origData = $object->getOrigData();

        if (empty($origData)) {
            $object->setData('create_date',Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $object->setData('update_date',Mage::helper('M2ePro')->getCurrentGmtDate());

        $result = parent::_beforeSave($object);

        // fix for \Varien_Db_Adapter_Pdo_Mysql::prepareColumnValue
        // an empty string cannot be saved -> NULL is saved instead
        // for Magento version > 1.6.x.x
        foreach ($object->getData() as $key => $value) {
            $value === '' && $object->setData($key,new Zend_Db_Expr("''"));
        }

        return $result;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        // fix for \Varien_Db_Adapter_Pdo_Mysql::prepareColumnValue
        // an empty string cannot be saved -> NULL is saved instead
        // for Magento version > 1.6.x.x
        foreach ($object->getData() as $key => $value) {
            if ($value instanceof Zend_Db_Expr && $value->__toString() === '\'\'') {
                $object->setData($key,'');
            }
        }

        return parent::_afterSave($object);
    }

    // ########################################
}