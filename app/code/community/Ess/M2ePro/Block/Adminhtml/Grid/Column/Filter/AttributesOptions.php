<?php

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Filter_AttributesOptions
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    // ####################################

    public function getHtml()
    {
        $id = $this->getColumn()->getId();
        $html = '<div id="attributes-options-filter_' . $id. '" class="attributes-options-filter">' .
                    '<div class="attributes-options-filter-selector">' .
                        '<select name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId() .
                        '" class="no-changes">';

        foreach ($this->_getOptions() as $option){
            $html .= $this->_renderOption($option, null);
        }

        $html .=        '</select>' . $this->getRemoveOptionButtonHtml() . '</div>' .
                    '<div class="attributes-options-filter-values">';

        $values = $this->getValue();
        if(is_array($values)) {
            $i = 0;
            foreach ($values as $option){
                if(is_array($option) && isset($option['value'])) {
                    $i++;
                    $html .= $this->renderAttrValue($i, $option);
                }
            }
        }

        $html .=    '</div>' .
               '</div>';
        return $html;
    }

    protected function _renderOption($option, $value)
    {
        $selected = (($option['label'] == $value && (!is_null($value))) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['label']).'"'.$selected.'>' .
            $this->escapeHtml($option['label']).'</option>';
    }

    protected function renderAttrValue($key, $option)
    {

        return '<div>
            <div>' . $option['attr'] . ': </div>
            <input type="text" name="' . $this->getColumn()->getId() .
                    '[' . $key . '][value]" value="' . $this->escapeHtml($option['value']) . '">
            <input type="hidden" name="' . $this->getColumn()->getId() .
                    '[' . $key . '][attr]" value="' . $this->escapeHtml($option['attr']) . '">' .
            $this->getRemoveOptionButtonHtml() .
        '</div>';
    }

    protected function getRemoveOptionButtonHtml()
    {
//        $src = Mage::getDesign()->getSkinUrl('images/rule_component_add.gif');
//        $html = ' <img src="' . $src . '" class="filter-param-add v-middle" alt="" style="display: none;"
//                                         title="' . Mage::helper('M2ePro')->__('Add') . '"/>';

        $src = Mage::getDesign()->getSkinUrl('M2ePro/images/rule_component_remove.gif');
        $html = '<img src="' . $src . '" class="filter-param-remove v-middle" alt="" style="display: none;"
                                         title="' . Mage::helper('M2ePro')->__('Remove') . '"/>';
        return $html;
    }

    public function getCondition()
    {
        $values = $this->getValue();
        $conditions = array();
        foreach ($values as $value) {
            $conditions[] = array('regexp'=> '"variation_product_options":[^}]*'.$value['attr'].'":"'.$value['value']);
        }
        return $conditions;
    }

    // ####################################
}