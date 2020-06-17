<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Dictionary_Multiselect extends
    Varien_Data_Form_Element_Abstract
{
    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('select');
        $this->setExtType('multiple');
        $this->setSize(4);
    }

    //########################################

    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name .= '[]';
        }
        return $name;
    }

    public function getElementHtml()
    {
        $this->addClass('select multiselect');
        $html = '';
        if ($this->getCanBeEmpty() && empty($this->_data['disabled'])) {
            $html .= '<input type="hidden" name="' . parent::getName() . '" value="" />';
        }
        $html .= '<select id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' .
            $this->serialize($this->getHtmlAttributes()) . ' multiple="multiple">' . "\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                if (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html .= $this->_optionToHtml($groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html .= '</select>' . "\n";
        $html .= $this->getAfterElementHtml();

        return $html;
    }

    public function getHtmlAttributes()
    {
        return array(
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'size',
            'tabindex',
            'data-min_values',
            'data-max_values'
        );
    }

    public function getDefaultHtml()
    {
        $result = ($this->getNoSpan() === true) ? '' : '<span class="field-row">' . "\n";
        $result .= $this->getLabelHtml();
        $result .= $this->getElementHtml();

        if ($this->getSelectAll() && $this->getDeselectAll()) {
            $result .= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.selectAll()">' .
                $this->getSelectAll() . '</a> <span class="separator">&nbsp;|&nbsp;</span>';
            $result .= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.deselectAll()">' .
                $this->getDeselectAll() . '</a>';
        }

        $result .= ($this->getNoSpan() === true) ? '' : '</span>' . "\n";

        $result .= '<script type="text/javascript">' . "\n";
        $result .= '   var ' . $this->getJsObjectName() . ' = {' . "\n";
        $result .= '     selectAll: function() { ' . "\n";
        $result .= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $result .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $result .= '             sel.options[i].selected = true; ' . "\n";
        $result .= '         } ' . "\n";
        $result .= '         return false; ' . "\n";
        $result .= '     },' . "\n";
        $result .= '     deselectAll: function() {' . "\n";
        $result .= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $result .= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $result .= '             sel.options[i].selected = false; ' . "\n";
        $result .= '         } ' . "\n";
        $result .= '         return false; ' . "\n";
        $result .= '     }' . "\n";
        $result .= '  }' . "\n";
        $result .= "\n" . '</script>';

        return $result;
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'ElementControl';
    }

    protected function _optionToHtml($option, $selected)
    {
        $html = '<option value="' . $this->_escape($option['value']) . '"';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html .= ' selected="selected"';
        }
        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        return $html;
    }

    //########################################
}
