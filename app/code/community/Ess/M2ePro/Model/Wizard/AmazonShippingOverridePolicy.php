<?php

class Ess_M2ePro_Model_Wizard_AmazonShippingOverridePolicy extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'marketplacesSynchronization',
    );

    // ########################################

    public function getNick()
    {
        return 'amazonShippingOverridePolicy';
    }

    // ########################################
}