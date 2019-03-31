<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_FullAmazonCategories extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'marketplacesSynchronization',
    );

    //########################################

    /**
     * @return string
     */
    public function getNick()
    {
        return 'fullAmazonCategories';
    }

    //########################################
}