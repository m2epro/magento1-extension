<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract */
    protected $_model;

    //########################################

    /**
     * @param Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract|Ess_M2ePro_Model_Abstract $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }

    //########################################

    public function getSnapshot()
    {
        $data = $this->getModel()->getData();

        if (empty($data)) {
            return array();
        }

        if (($this->getModel() instanceof Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract ||
             $this->getModel() instanceof Ess_M2ePro_Model_Component_Parent_Abstract) &&
            $this->getModel()->getChildObject() !== null
        ) {
            $data = array_merge($data, $this->getModel()->getChildObject()->getData());
        }

        foreach ($data as &$value) {
            (null !== $value && !is_array($value)) && $value = (string)$value;
        }

        return $data;
    }

    //########################################

    protected function sanitizeData(array &$snapshot)
    {
        unset($snapshot['id'], $snapshot['create_date'], $snapshot['update_date']);
    }

    //########################################
}
