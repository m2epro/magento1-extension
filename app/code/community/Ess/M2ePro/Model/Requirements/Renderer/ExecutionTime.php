<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Requirements_Checks_ExecutionTime getCheckObject()
 */

class Ess_M2ePro_Model_Requirements_Renderer_ExecutionTime extends Ess_M2ePro_Model_Requirements_Renderer_Abstract
{
    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Max Execution Time');
    }

    // ---------------------------------------

    public function getMin()
    {
        return <<<HTML
<span style="color: grey;">
      <span>{$this->getCheckObject()->getMin()}</span>&nbsp;/
      <span>{$this->getCheckObject()->getReader()->getExecutionTimeData('recommended')}</span>&nbsp;
      <span>{$this->getCheckObject()->getReader()->getExecutionTimeData('measure')}</span>
</span>
HTML;
    }

    public function getReal()
    {
        $color = $this->getCheckObject()->isMeet() ? 'green' : 'red';

        if (is_null($this->getCheckObject()->getReal())) {
            $value = Mage::helper('M2ePro')->__('unknown');
            $html = <<<HTML
<span style="color: {$color};">
    <span>{$value}</span>&nbsp;
</span>
HTML;
        } else if ($this->getCheckObject()->getReal() <= 0) {
            $value = Mage::helper('M2ePro')->__('unlimited');
            $html = <<<HTML
<span style="color: {$color};">
    <span>{$value}</span>&nbsp;
</span>
HTML;
        } else {
            $html = <<<HTML
<span style="color: {$color};">
    <span>{$this->getCheckObject()->getReal()}</span>&nbsp;
    <span>{$this->getCheckObject()->getReader()->getExecutionTimeData('measure')}</span>
</span>
HTML;
        }

        if (Mage::helper('M2ePro/Client')->isPhpApiFastCgi()) {

            $noticeImage = Mage::getDesign()->getSkinUrl('M2ePro/images/tool-tip-icon.png');
            $helpImage = Mage::getDesign()->getSkinUrl('M2ePro/images/help.png');
            $notice = Mage::helper('M2ePro')->__(
                'PHP is running using <b>fast CGI</b> Module on your web Server.<br/>
                 It has its own Settings that override max_execution_time in php.ini or .htaccess.'
            );

            $html .= <<<HTML
<img src="{$noticeImage}" class="tool-tip-image">
<span class="tool-tip-message" style="display: none;">
    <img src="{$helpImage}">
    <span>{$notice}</span>
</span>
HTML;
        }

        return $html;
    }

    //########################################
}