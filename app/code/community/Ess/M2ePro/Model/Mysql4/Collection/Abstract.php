<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Mysql4_Collection_Abstract
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    // ########################################

    protected function _toOptionArray($valueField = 'id', $labelField = 'title', $additional = array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    protected function _toOptionHash($valueField = 'id', $labelField = 'title')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    // ########################################
}