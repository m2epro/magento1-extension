<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Result
{
    /** @var bool */
    private $status;

    /** @var string */
    private $errorMessage;

    /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Issue[] */
    private $issues;

    public function __construct($args)
    {
        $this->status = $args['status'];
        $this->errorMessage = $args['errorMessage'];
        $this->issues = $args['issues'];
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Issue[]
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
