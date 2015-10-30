<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Helper_Data_Cache_Abstract extends Mage_Core_Helper_Abstract
{
    //########################################

    abstract public function getValue($key);

    abstract public function setValue($key, $value, array $tags = array(), $lifetime = null);

    //########################################

    abstract public function removeValue($key);

    abstract public function removeTagValues($tag);

    abstract public function removeAllValues();

    //########################################
}