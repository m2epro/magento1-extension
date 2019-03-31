<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Description_SnapshotBuilder
    extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->model->getData();
        if (empty($data)) {
            return array();
        }

        $data['specifics'] = $this->model->getChildObject()->getSpecifics();
        $data['definition'] = $this->model->getChildObject()->getDefinitionTemplate()
            ? $this->model->getChildObject()->getDefinitionTemplate()->getData() : array();

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

                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }
        unset($value);

        foreach ($data['definition'] as $key => &$value) {
            if (in_array($key, $ignoredKeys)) {
                unset($data['definition'][$key]);
                continue;
            }

            if (is_numeric($value) && $value == 0.0) {
                $value = (float)$value;
            }
        }

        return $data;
    }

    //########################################
}