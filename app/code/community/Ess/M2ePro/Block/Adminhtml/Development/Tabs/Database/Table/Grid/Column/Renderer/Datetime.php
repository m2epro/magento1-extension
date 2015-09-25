<?php

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid_Column_Renderer_Datetime extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
    // ####################################

    public function render(Varien_Object $row)
    {
        if ($data = $this->_getValue($row)) {
            return $data;
        }
        return $this->getColumn()->getDefault();
    }

    // ####################################
}
