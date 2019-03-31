<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher extends Ess_M2ePro_Block_Adminhtml_Switcher
{
    const LISTING_TYPE_M2E_PRO       = 'm2epro';
    const LISTING_TYPE_LISTING_OTHER = 'other';

    protected $paramName = 'listing_type';

    public $showM2eProOption = true;
    public $showOtherOption  = true;

    //########################################

    public function getLabel()
    {
        return $this->__('Listing Type');
    }

    protected function loadItems()
    {
        if ($this->showM2eProOption) {

            $this->items['mode']['value'][] = array(
                'label' => $this->__('M2E Pro'),
                'value' => self::LISTING_TYPE_M2E_PRO
            );
        }

        if ($this->showOtherOption) {

            $this->items['mode']['value'][] = array(
                'label' => $this->__('3rd Party'),
                'value' => self::LISTING_TYPE_LISTING_OTHER
            );
        }

        if (count($this->items['mode']['value']) < 2) {
            $this->setIsDisabled(true);
        }

        $this->hasDefaultOption(false);
    }

    //########################################
}