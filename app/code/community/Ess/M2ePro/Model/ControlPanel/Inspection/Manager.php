<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Manager
{
    const GROUP_ORDERS    = 'orders';
    const GROUP_PRODUCTS  = 'products';
    const GROUP_STRUCTURE = 'structure';
    const GROUP_GENERAL   = 'general';

    const EXECUTION_SPEED_SLOW = 'slow';
    const EXECUTION_SPEED_FAST = 'fast';

    /** @var array */
    protected $_inspections = array();

    /** @var array */
    protected $_byExecution = array();

    /** @var array */
    protected $_byGroup = array();

    //########################################

    public function __construct()
    {
        $this->initInspections('Inspector');
    }

    protected function initInspections($dirName)
    {
        $directoryIterator = new DirectoryIterator(__DIR__ .DS. $dirName);
        foreach ($directoryIterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $model */
            $modelName = "M2ePro/ControlPanel_Inspection_{$dirName}_" . str_replace('.php', '', $item->getFilename());
            $model = Mage::getModel($modelName);
            if (!$model instanceof Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection) {
                continue;
            }

            $id = $this->getId($model);

            $this->_inspections[$id] = $model;

            $this->_byExecution[$model->getGroup()][] = $id;
            $this->_byGroup[$model->getExecutionSpeed()][] = $id;
        }
    }

    //########################################

    /**
     * @param array|null $keys
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection[]
     */
    public function getInspections($keys = null)
    {
        if ($keys === null) {
            return $this->_inspections;
        }

        $inspections = array();
        foreach ($keys as $key) {
            $inspections[$key] = $this->getInspection($key);
        }

        return $inspections;
    }

    /**
     * @param string $type
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection[]
     */
    public function getInspectionsByGroup($type)
    {
        if (!isset($this->_byGroup[$type])) {
            return array();
        }

        return $this->getInspections($this->_byGroup[$type]);
    }

    /**
     * @param string $type
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection[]
     */
    public function getInspectionsByExecutionSpeed($type)
    {
        if (!isset($this->_byExecution[$type])) {
            return array();
        }

        return $this->getInspections($this->_byExecution[$type]);
    }

    /**
     * @param string $className
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
     * @throws Exception
     */
    public function getInspection($className)
    {
        if (!isset($this->_inspections[$className])) {
            throw new Ess_M2ePro_Model_Exception_Logic("No such inspection {$className}.");
        }

        return $this->_inspections[$className];
    }

    //########################################

    public function getId(Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $inspection)
    {
        return get_class($inspection);
    }

    //########################################
}