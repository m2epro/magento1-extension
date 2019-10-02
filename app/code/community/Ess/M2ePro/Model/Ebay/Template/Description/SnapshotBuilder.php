<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Description_SnapshotBuilder
    extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->_model->getData();

        foreach ($data as &$value) {
            $value !== null && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    //########################################
}
