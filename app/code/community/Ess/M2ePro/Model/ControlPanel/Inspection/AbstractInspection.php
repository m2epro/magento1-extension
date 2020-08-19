<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
{
    /** @var array */
    protected $_params = array();

    /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Result[]|null */
    protected $_results;

    /** @var float */
    protected $_timeToExecute = 0.00;

    //########################################

    public function __construct(array $_params = array())
    {
        $this->_params = $_params;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Result[]
     */
    abstract protected function process();

    //########################################

    /**
     * @return string
     */
    abstract public function getTitle();

    /**
     * @return string
     */
    abstract public function getGroup();

    /**
     * @return string
     */
    abstract public function getExecutionSpeed();

    /**
     * @return string
     */
    public function getDescription()
    {
        return null;
    }

    //########################################

    public function execute()
    {
        $start = microtime(true);
        $this->_results = $this->process();
        $this->_timeToExecute = round(microtime(true) - $start, 2);

        if (empty($this->_results)) {
            $this->_results[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createSuccess(
                $this
            );
        }
    }

    //########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @return float
     */
    public function getTimeToExecute()
    {
        return $this->_timeToExecute;
    }

    //########################################

    public function getResults()
    {
        if ($this->_results === null) {
            $this->execute();
        }

        return $this->_results;
    }

    public function getState()
    {
        $state = 0;
        foreach ($this->getResults() as $result) {
            $result->getState() > $state && $state = $result->getState();
        }

        return $state;
    }

    //########################################
}