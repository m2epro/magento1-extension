<?php

use Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping as Resource;

class Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping extends Ess_M2ePro_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_Dictionary_TemplateShipping');
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->getData(Resource::COLUMN_TEMPLATE_ID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(Resource::COLUMN_TITLE);
    }
}