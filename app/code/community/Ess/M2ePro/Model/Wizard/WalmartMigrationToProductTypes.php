<?php

class Ess_M2ePro_Model_Wizard_WalmartMigrationToProductTypes extends Ess_M2ePro_Model_Wizard
{
    const NICK = 'walmartMigrationToProductTypes';

    public function isActive($view)
    {
        return Mage::helper('M2ePro/Component_Walmart')->isEnabled();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return self::NICK;
    }
}