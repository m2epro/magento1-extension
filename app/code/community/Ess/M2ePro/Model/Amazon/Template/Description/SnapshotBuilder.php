<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getModel()
 */
class Ess_M2ePro_Model_Amazon_Template_Description_SnapshotBuilder
    extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();
        if (empty($data)) {
            return array();
        }

        $data['specifics'] = $this->getModel()->getChildObject()->getSpecifics();
        $data['definition'] = $this->getModel()->getChildObject()->getDefinitionTemplate()
            ? $this->getModel()->getChildObject()->getDefinitionTemplate()->getData() : array();

        $ignoredKeys = array(
            'id', 'template_description_id',
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

        foreach ($data['definition'] as $key => &$value) {
            if (in_array($key, $ignoredKeys)) {
                unset($data['definition'][$key]);
                continue;
            }

            if (is_numeric($value) && $value == 0.0) {
                $value = (float)$value;
            }
        }

        unset($value);

        return $data;
    }

    //########################################
}
