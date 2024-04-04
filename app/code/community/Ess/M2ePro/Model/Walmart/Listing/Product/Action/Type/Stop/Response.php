<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract as ChangeProcessor;

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Response
{
    //########################################

    /**
     * @param array $params
     */
    public function processSuccess($params = array())
    {
        $data = array();

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data);
        $data = $this->appendLagTimeValues($data);

        $isStoppedManually = $this->getListingProduct()->isInactive() && $this->getListingProduct()->getStatusChanger()
            === Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $data = $this->appendIsStoppedManually($data, $isStoppedManually);

        $this->getListingProduct()->addData($data);

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    protected function setLastSynchronizationDates()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['last_synchronization_dates']['qty'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->getListingProduct()->setSettings('additional_data', $additionalData);
    }

    //########################################

    public function throwRepeatActionInstructions()
    {
        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add(
            array(
                array(
                    'listing_product_id' => $this->getListingProduct()->getId(),
                    'type'               => ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 80
                )
            )
        );
    }

    //########################################
}
