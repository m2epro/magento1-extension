<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Registration_Manager
{
    /**
     * @return Ess_M2ePro_Model_Registration_Info
     */
    public function getInfo()
    {
        $data = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson('/registration/user_info/');

        return Mage::getSingleton('M2ePro/Registration_Info_Factory')->createInfoInstance(
            isset($data['email']) ? $data['email'] : null,
            isset($data['firstname']) ? $data['firstname'] : null,
            isset($data['lastname']) ? $data['lastname'] : null,
            isset($data['phone']) ? $data['phone'] : null,
            isset($data['country']) ? $data['country'] : null,
            isset($data['city']) ? $data['city'] : null,
            isset($data['postal_code']) ? $data['postal_code'] : null
        );
    }

    /**
     * @param Ess_M2ePro_Model_Registration_Info $info
     * @return void
     */
    public function saveInfo(Ess_M2ePro_Model_Registration_Info $info)
    {
        $data = array(
            'email'       => $info->getEmail(),
            'firstname'   => $info->getFirstname(),
            'lastname'    => $info->getLastname(),
            'phone'       => $info->getPhone(),
            'country'     => $info->getCountry(),
            'city'        => $info->getCity(),
            'postal_code' => $info->getPostalCode(),
        );

        Mage::helper('M2ePro/Module')->getRegistry()->setValue('/registration/user_info/', $data);
    }

    /**
     * @return bool
     */
    public function isExistInfo()
    {
        $userInfo = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson('/registration/user_info/');
        return !empty($userInfo);
    }
}
