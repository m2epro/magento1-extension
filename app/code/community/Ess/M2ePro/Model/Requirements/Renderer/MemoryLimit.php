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

    public function getAdditional()
    {
        $helper = Mage::helper('M2ePro');
        $testUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_support/testMemoryLimit');
        $testResultUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_support/testMemoryLimitResult');

        return <<<HTML
<script>

function memoryLimitTest()
{
    new Ajax.Request('{$testUrl}', {
        method: 'post',
        asynchronous: true,
        onComplete: function(transport) {
            
            new Ajax.Request('{$testResultUrl}', {
                method: 'post',
                asynchronous: true,
                onComplete: function(transport) {
                    
                    MessageObj.clearAll();
                    var response = transport.responseText.evalJSON();
                    if (typeof response['result'] === 'undefined') {
                        MessageObj.addError('{$helper->__('Something went wrong. Please try again later.')}');
                        return;
                    }
                    
                    if (response['result'] < {$this->getCheckObject()->getMin()}) {
                        MessageObj.addWarning(
                            '{$this->getTestWarningMessage()}'
                            .replace('%value%', response['result'])
                            .replace('%min-value%', '{$this->getCheckObject()->getMin()}')
                        );
                    } else {
                        MessageObj.addSuccess(
                            '{$helper->__('Actual memory limit is %value% Mb.')}'
                            .replace('%value%', response['result'])
                        );
                    }
                }
            });
        }
    });
}

</script>

<a class="button-link button-link-grey" href="#" onclick="memoryLimitTest()">{$helper->__('Check')}</a>&nbsp;
HTML;
    }

    protected function getTestWarningMessage()
    {
        return Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__(
                'Actual memory limit is %value% Mb. It should be increased to at least %min-value% Mb
                for uninterrupted synchronization work.'
            )
        );
    }

    //########################################
}
