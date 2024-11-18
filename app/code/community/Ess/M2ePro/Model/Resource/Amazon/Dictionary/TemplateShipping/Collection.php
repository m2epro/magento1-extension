<?php

class Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_Dictionary_TemplateShipping');
    }
}