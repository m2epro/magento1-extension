<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Filter_CategoryMode
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    const MODE_NOT_SELECTED = 0;
    const MODE_SELECTED     = 1;
    const MODE_EBAY         = 2;
    const MODE_ATTRIBUTE    = 3;
    const MODE_TITLE        = 10;

    //########################################

    public function getHtml()
    {
        $helper = Mage::helper('M2ePro');
        $value = $this->getValue();

        $titleValue = !empty($value['title']) ? $value['title'] : '';
        $isAjax = $helper->jsonEncode($this->getRequest()->isAjax());
        $modeTitle = self::MODE_TITLE;

        $html = <<<HTML
<script>
    
    (function() {

        var initObservers = function () {
         
         $('{$this->_getHtmlId()}')
            .observe('change', function() {
                
                var div = $('{$this->_getHtmlId()}_title_container');
                div.hide();
                
                if (this.value == '{$modeTitle}') {
                    div.show();
                }
            })
            .simulate('change');
         };
         
         Event.observe(window, 'load', initObservers);
         if ({$isAjax}) {
             initObservers();
         }
     
    })();
     
</script>

<div id="{$this->_getHtmlId()}_title_container" style="display: none;">
    <div style="width: auto; padding-top: 5px;">
        <span>{$helper->__('Category Path / Category ID')}: </span><br>
        <input style="width: 300px;" type="text" value="{$titleValue}" name="{$this->getColumn()->getId()}[title]">
    </div>
</div>
HTML;

        return parent::getHtml() . $html;
    }

    //########################################

    public function getValue()
    {
        $value = $this->getData('value');

        if (is_array($value) &&
            (isset($value['mode']) && $value['mode'] !== null) ||
            (isset($value['title']) && !empty($value['mode']))
        ) {
            return $value;
        }

        return NULL;
    }

    //########################################

    protected function _renderOption($option, $value)
    {
        $value = isset($value['mode']) ? $value['mode'] : null;
        return parent::_renderOption($option, $value);
    }

    protected function _getHtmlName()
    {
        return "{$this->getColumn()->getId()}[mode]";
    }

    //########################################
}
