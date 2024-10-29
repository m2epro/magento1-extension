<?php

class Ess_M2ePro_Model_Walmart_ProductType_Service_ResultOfMassDelete
{
    private $countDeleted = 0;
    private $countLocked = 0;

    /**
     * @return void
     */
    public function incrementCountDeleted()
    {
        $this->countDeleted++;
    }

    /**
     * @return void
     */
    public function incrementCountLocked()
    {
        $this->countLocked++;
    }

    /**
     * @return int
     */
    public function getCountDeleted()
    {
        return $this->countDeleted;
    }

    /**
     * @return int
     */
    public function getCountLocked()
    {
        return $this->countLocked;
    }
}
