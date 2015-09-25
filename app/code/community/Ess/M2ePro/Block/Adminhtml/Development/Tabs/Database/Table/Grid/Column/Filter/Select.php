<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid_Column_Filter_Select
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    // ####################################

    protected function _getOptions()
    {
        $options = array();

        $modelName = $this->getColumn()->getGrid()->modelName;
        $htmlName = $this->_getHtmlName();

        $colOptions = Mage::getModel('M2ePro/'.$modelName)
            ->getCollection()
            ->getSelect()
            ->group($htmlName)
            ->query();

        if (!empty($colOptions)) {
            $options = array(array('value' => null, 'label' => ''));
            foreach ($colOptions as $colOption) {
                $options[] = array(
                    'value' => $colOption[$htmlName],
                    'label' => $colOption[$htmlName],
                );
            }
        }

        return $options;
    }

    // ####################################
}