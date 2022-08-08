<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Server_Create_Result
{
    /** @var string */
    private $hash;
    /** @var array */
    private $info;

    public function __construct($hash, $info)
    {
        $this->hash = $hash;
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }
}
