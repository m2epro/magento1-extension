<?php

class Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Shipping');
    }
}
