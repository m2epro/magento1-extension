<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Button_DropDown extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/widget/button/dropdown.phtml';

    //########################################

    public function getTargetCssClass()
    {
        if (empty($this->_data['target_css_class'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Target CSS Class is not set.');
        }

        return $this->_data['target_css_class'];
    }

    public function getDropDownHtml()
    {
        $html = '';

        foreach ($this->getItems() as $item) {
            if (!isset($item['url'])) {
                throw new InvalidArgumentException('Item url is not set.');
            }
            if (!isset($item['label'])) {
                throw new InvalidArgumentException('Item label is not set');
            }

            $url = $item['url'];
            $label = $item['label'];
            $target = isset($item['target']) ? $item['target'] : '_self';
            $onclick = isset($item['onclick']) ? $item['onclick'] : '';

            $style = (string)$this->getStyle();

            $html .= <<<HTML
<li href="{$url}" target="{$target}" onclick="{$onclick}">{$label}</li>
HTML;
        }

        if ($html) {
            $html = "<ul style=\"{$style}\">{$html}</ul>";
        }

        return $html;
    }

    private function getItems()
    {
        if (empty($this->_data['items']) || !is_array($this->_data['items'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Items are not set.');
        }

        return $this->_data['items'];
    }

    //########################################
}