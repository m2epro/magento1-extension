<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_MigrationToMagento2Controller
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function disableModuleAction()
    {
        /** @var Ess_M2ePro_Model_Upgrade_MigrationToMagento2_Runner $migration */
        $migration = Mage::getModel('M2ePro/Upgrade_MigrationToMagento2_Runner');
        $migration->initialize();

        try {
            $migration->run();
        } catch (Exception $exception) {
            $this->getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'M2E Pro was not disabled. Reason: %error_message%.', $exception->getMessage()
                )
            );

            return $this->_redirect('adminhtml/dashboard');
        }

        $migration->complete();

        $this->getSession()->addSuccess(
            Mage::helper('M2ePro')->__('M2E Pro was successfully disabled.')
        );
        return $this->_redirect('adminhtml/dashboard');
    }

    //########################################
}
