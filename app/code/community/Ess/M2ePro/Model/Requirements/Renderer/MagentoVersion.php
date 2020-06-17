<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Requirements_Checks_MagentoVersion getCheckObject()
 */

class Ess_M2ePro_Model_Requirements_Renderer_MagentoVersion extends Ess_M2ePro_Model_Requirements_Renderer_Abstract
{
    /** @var bool */
    protected $_hideIfMeet = true;

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Magento Version');
    }

    // ---------------------------------------

    public function getMin()
    {
        return <<<HTML
<span style="color: grey;">
      <span>{$this->getCheckObject()->getMin()}</span>
</span>
HTML;
    }

    public function getReal()
    {
        $color = $this->getCheckObject()->isMeet() ? 'green' : 'red';
        return <<<HTML
<span style="color: {$color};">
    <span>{$this->getCheckObject()->getReal()}</span>&nbsp;
</span>
HTML;
    }

    public function getAdditional()
    {
        return '';
    }

    //########################################
}
