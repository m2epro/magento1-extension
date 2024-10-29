<?php

class Ess_M2ePro_Block_Adminhtml_Wizard_WalmartMigrationToProductTypes_Content
    extends Mage_Adminhtml_Block_Abstract
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartMigrationToProductTypes_content');
        $this->setTemplate('M2ePro/wizard/walmartMigrationToProductTypes/content.phtml');
    }

    /**
     * @return string
     */
    public function getSyncUrl()
    {
        return $this->getUrl('*/*/sync');
    }
}