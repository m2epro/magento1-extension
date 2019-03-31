<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Runner
{
    // ##################################

    public function getType()
    {
        return Ess_M2ePro_Model_Processing::TYPE_SINGLE;
    }

    // ##################################

    public function processSuccess()
    {
        try {
            $this->getResponser()->process();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
        }

        return true;
    }

    public function processExpired()
    {
        $this->getResponser()->failDetected($this->getExpiredErrorMessage());
    }

    public function complete()
    {
        try {
            parent::complete();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
            throw $exception;
        }
    }

    // ##################################

    protected function eventBefore()
    {
        parent::eventBefore();

        $params = $this->getParams();

        $requestPendingSingle = Mage::getResourceModel('M2ePro/Request_Pending_Single_Collection');
        $requestPendingSingle->addFieldToFilter('component', $params['component']);
        $requestPendingSingle->addFieldToFilter('server_hash', $params['server_hash']);

        /** @var Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle */
        $requestPendingSingle = $requestPendingSingle->getFirstItem();

        if (!$requestPendingSingle->getId()) {
            $requestPendingSingle->setData(array(
                'component'       => $params['component'],
                'server_hash'     => $params['server_hash'],
                'expiration_date' => Mage::helper('M2ePro')->getDate(
                    Mage::helper('M2ePro')->getCurrentGmtDate(true)+static::PENDING_REQUEST_MAX_LIFE_TIME
                )
            ));

            $requestPendingSingle->save();
        }

        $processingSingle = Mage::getModel('M2ePro/Connector_Command_Pending_Processing_Single');
        $processingSingle->setData(array(
            'processing_id'             => $this->getProcessingObject()->getId(),
            'request_pending_single_id' => $requestPendingSingle->getId(),
        ));

        $processingSingle->save();
    }

    // ##################################
}