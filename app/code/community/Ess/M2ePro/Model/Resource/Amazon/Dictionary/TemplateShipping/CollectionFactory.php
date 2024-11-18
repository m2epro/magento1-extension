<?php

class Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping_Collection */
        return Mage::getResourceModel('M2ePro/Amazon_Dictionary_TemplateShipping_Collection');
    }
}