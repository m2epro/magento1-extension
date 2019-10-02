<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Requirements_Checks_MemoryLimit getCheckObject()
 */

class Ess_M2ePro_Model_Requirements_Renderer_MemoryLimit extends Ess_M2ePro_Model_Requirements_Renderer_Abstract
{
    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Memory Limit');
    }

    // ---------------------------------------

    public function getMin()
    {
        return <<<HTML
<span style="color: grey;">
      <span>{$this->getCheckObject()->getMin()}</span>&nbsp;/
      <span>{$this->getCheckObject()->getReader()->getMemoryLimitData('recommended')}</span>&nbsp;
      <span>{$this->getCheckObject()->getReader()->getMemoryLimitData('measure')}</span>
</span>
HTML;
    }

    public function getReal()
    {
        $color = $this->getCheckObject()->isMeet() ? 'green' : 'red';
        return <<<HTML
<span style="color: {$color};">
    <span>{$this->getCheckObject()->getReal()}</span>&nbsp;
    <span>{$this->getCheckObject()->getReader()->getMemoryLimitData('measure')}</span>
</span>
HTML;
    }

    //########################################
}
