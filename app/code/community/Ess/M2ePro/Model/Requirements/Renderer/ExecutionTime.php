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
        $helper = Mage::helper('M2ePro');
        $color = $this->getCheckObject()->isMeet() ? 'green' : 'red';

        if ($this->getCheckObject()->getReal() === null) {
            $url = Mage::helper('M2ePro/Module_Support')->getKnowledgeBaseUrl('1563888');
            $html = <<<HTML
<span style="color: orange;">
    <span>{$helper->__('unknown')}</span>&nbsp;
    <a href="{$url}" target="_blank">{$helper->__('[read more]')}</a>&nbsp;
</span>
HTML;
        } else if ($this->getCheckObject()->getReal() <= 0) {
            $html = <<<HTML
<span style="color: {$color};">
    <span>{$helper->__('unlimited')}</span>&nbsp;
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

        return $html;
    }

    public function getAdditional()
    {
        $helper = Mage::helper('M2ePro');
        $testUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_support/testExecutionTime');
        $testResultUrl = Mage::helper('adminhtml')->getUrl('*/adminhtml_support/testExecutionTimeResult');

        return <<<HTML
<script>
function executionTimeTest(seconds)
{
    seconds = parseInt(seconds);
    if (isNaN(seconds) || seconds <= 0) {
        return false;
    }
    
    Windows.getFocusedWindow().close();
    
    new Ajax.Request('{$testUrl}', {
        method: 'post',
        asynchronous: true,
        parameters: { seconds: seconds },
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
                            '{$helper->__('Max execution time of %value% sec. is tested.')}'
                            .replace('%value%', response['result'])
                        );
                    }
                }
            });
        }
    });
}

function openExecutionTimeTestPopup()
{
    var popup = Dialog.info('{$this->getPopupHtml()}', {
        draggable: true,
        resizable: true,
        closable: true,
        className: "magento",
        windowClassName: "popup-window",
        title: '{$helper->__('Check execution time:')}',
        top: 50,
        width: 550,
        height: 320,
        zIndex: 100,
        border: false,
        hideEffect: Element.hide,
        showEffect: Element.show
    });
    popup.options.destroyOnClose = true;
    CommonObj.autoHeightFix();
}
</script>

<a class="button-link button-link-grey" href="#" onclick="openExecutionTimeTestPopup()">{$helper->__('Check')}</a>&nbsp;
HTML;
    }

    protected function getPopupHtml()
    {
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Check'),
            'onclick' => "executionTimeTest($('execution_time_value').value)",
        );
        $button = Mage::app()->getLayout()->createBlock('adminhtml/widget_button')->setData($data);

        $helper = Mage::helper('M2ePro');
        return $helper->escapeJs(<<<HTML
<div style="margin-top: 10px;">
    {$helper->__(
        'Enter the time you want to test. The minimum recommended value is %min% sec.<br>
        The Module interface will be unavailable during the check.
        Synchronization processes won’t be affected.',
        $this->getCheckObject()->getMin()
    )}
    <br><br>
    <label>{$helper->__('Seconds')}</label>:&nbsp;
    <input type="text" id="execution_time_value" value="{$this->getCheckObject()->getMin()}" />
</div>

<div style="margin-top: 10px; margin-bottom: 20px; text-align: right;">
    {$button->toHtml()}
</div>
HTML
        );
    }

    protected function getTestWarningMessage()
    {
        return Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__(
                'Max execution time of %value% sec. is tested. To ensure your execution time limit is
                sufficient, the test should be run for at least %min-value% sec.'
            )
        );
    }

    //########################################
}
