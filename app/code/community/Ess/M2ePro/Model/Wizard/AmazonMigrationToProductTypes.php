<?php

class Ess_M2ePro_Model_Wizard_AmazonMigrationToProductTypes extends Ess_M2ePro_Model_Wizard
{
    const NICK = 'amazonMigrationToProductTypes';

    public function isActive($view)
    {
        return Mage::helper('M2ePro/Component_Amazon')->isEnabled();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return self::NICK;
    }
}