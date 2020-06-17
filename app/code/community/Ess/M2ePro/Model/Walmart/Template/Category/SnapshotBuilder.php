<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Template_Category getModel()
 */
class Ess_M2ePro_Model_Walmart_Template_Category_SnapshotBuilder
    extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();
        if (empty($data)) {
            return array();
        }

        $data['specifics'] = $this->getModel()->getSpecifics();

        $ignoredKeys = array(
            'id', 'title', 'template_category_id',
            'update_date', 'create_date',
        );

        foreach ($data['specifics'] as &$specificsData) {
            foreach ($specificsData as $key => &$value) {
                if (in_array($key, $ignoredKeys)) {
                    unset($specificsData[$key]);
                    continue;
                }

                $value !== null && !is_array($value) && $value = (string)$value;
            }

            unset($value);
        }

        unset($specificsData);

        return $data;
    }

    //########################################
}
