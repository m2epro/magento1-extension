<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Cron extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'cron';
    }

    //########################################

    /**
     * @return bool
     */
    public function isAllowed()
    {
        $helper = Mage::helper('M2ePro/Module_Cron');

        if ($this->getInitiator() === Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return true;
        }

        if ($helper->getLastRun() === null) {
            return true;
        }

        if ($helper->isRunnerService() && $helper->isLastRunMoreThan(900)) {
            return true;
        }

        if ($helper->isRunnerMagento()) {
            /** @var Ess_M2ePro_Helper_Data $dateHelper */
            $dateHelper = Mage::helper('M2ePro');

            $currentTimeStamp = $dateHelper->getCurrentGmtDate(true);
            $lastTypeChange = $helper->getLastRunnerChange();
            $lastRun = Mage::helper('M2ePro/Module')->getRegistry()->getValue('/servicing/cron/last_run/');

            if (($lastTypeChange === null ||
                    $currentTimeStamp > (int)$dateHelper->createGmtDateTime($lastTypeChange)->format('U') + 86400) &&
                ($lastRun === null ||
                    $currentTimeStamp > (int)$dateHelper->createGmtDateTime($lastRun)->format('U') + 86400)
            ) {
                Mage::helper('M2ePro/Module')->getRegistry()->setValue(
                    '/servicing/cron/last_run/',
                    $dateHelper->getCurrentGmtDate()
                );

                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getRequestData()
    {
        $adminStore = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        return array(
            'base_url' => $adminStore->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, null),
            'calculation_url' => $adminStore->getUrl(
                'M2ePro/cron/test',
                array(
                    '_use_rewrite' => true,
                    '_nosid' => true,
                    '_secure' => false
                )
            )
        );
    }

    public function processResponseData(array $data)
    {
        if (!isset($data['auth_key'])) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()
                                     ->setGroupValue('/cron/service/', 'auth_key', $data['auth_key']);
    }

    //########################################
}
