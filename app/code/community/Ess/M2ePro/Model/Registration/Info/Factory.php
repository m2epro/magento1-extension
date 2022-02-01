<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Registration_Info_Factory
{
    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $phone
     * @param string $country
     * @param string $city
     * @param string $postalCode
     * @return Ess_M2ePro_Model_Registration_Info
     */
    public function createInfoInstance(
        $email,
        $firstname,
        $lastname,
        $phone,
        $country,
        $city,
        $postalCode
    ) {
        return new Ess_M2ePro_Model_Registration_Info(
            $email,
            $firstname,
            $lastname,
            $phone,
            $country,
            $city,
            $postalCode
        );

    }
}
