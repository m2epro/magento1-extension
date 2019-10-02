<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_SnapshotBuilder extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->_model->getData();
        if (empty($data)) {
            return array();
        }

        $data['specifics'] = $this->_model->getSpecifics();

        foreach ($data['specifics'] as &$specificData) {
            unset($specificData['id'], $specificData['template_category_id']);
            foreach ($specificData as &$value) {
                $value !== null && !is_array($value) && $value = (string)$value;
            }
        }

        return $data;
    }

    //########################################
}
