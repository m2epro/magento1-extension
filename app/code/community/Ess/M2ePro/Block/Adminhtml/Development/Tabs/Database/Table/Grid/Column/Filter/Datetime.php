<?php

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid_Column_Filter_Datetime
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Datetime
{
    // ####################################

    public function getHtml()
    {
        $htmlId = $this->_getHtmlId() . microtime(true);
        $imageUrl = Mage::getDesign()->getSkinUrl('M2ePro/images/grid-cal.gif');
        $utcLocaleCode = Mage_Core_Model_Locale::DEFAULT_TIMEZONE;

        return <<<HTML
<div class="range">
    <div class="range-line date" style="width: 185px;">
        <span class="label">From:</span>
        <input type="text" name="{$this->_getHtmlName()}[from]" id="{$htmlId}_from"
               value="{$this->getEscapedValue('from')}" class="input-text no-changes"
               style="width: 120px !important;" />
        <img src="{$imageUrl}" alt="" class="v-middle" id="{$htmlId}_from_trig" title="Date selector" />
    </div>

    <div class="range-line date" style="width: 185px;">
        <span class="label">To:</span>
        <input type="text" name="{$this->_getHtmlName()}[to]" id="{$htmlId}_to"
               value="{$this->getEscapedValue('to')}" class="input-text no-changes"
               style="width: 120px !important;" />
        <img src="{$imageUrl}" alt="" class="v-middle" id="{$htmlId}_to_trig" title="Date selector" />
    </div>

    <input type="hidden" name="{$this->_getHtmlName()}[locale]" value="{$utcLocaleCode}"/>
</div>

<script type="text/javascript">

    Calendar.setup({
        inputField : "{$htmlId}_from",
        ifFormat : "%Y-%m-%d %H:%M:00",
        button : "{$htmlId}_from_trig",
        showsTime: true,
        align : "Bl",
        singleClick : true
    });

    Calendar.setup({
        inputField : "{$htmlId}_to",
        ifFormat : "%Y-%m-%d %H:%M:00",
        button : "{$htmlId}_to_trig",
        showsTime: true,
        align : "Bl",
        singleClick : true
    });

</script>
HTML;
    }

    protected function _convertDate($date, $locale)
    {
        return $date;
    }

    public function getEscapedValue($index = null)
    {
        return $this->getValue($index);
    }

    // ####################################
}
