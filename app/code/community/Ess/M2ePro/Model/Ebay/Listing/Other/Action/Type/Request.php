<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
{
    /**
     * @var array
     */
    private $requestsTypes = array(
        'selling',
        'description',
    );

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Abstract]
     */
    private $requests = array();

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $this->beforeBuildDataEvent();

        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectRequestsWarningMessages();

        return $data;
    }

    // ---------------------------------------

    abstract protected function getActionData();

    //########################################

    protected function beforeBuildDataEvent() {}

    // ---------------------------------------

    protected function prepareFinalData(array $data)
    {
        return $data;
    }

    protected function collectRequestsWarningMessages()
    {
        foreach ($this->requestsTypes as $requestType) {

            $messages = $this->getRequest($requestType)->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Selling
     */
    public function getRequestSelling()
    {
        return $this->getRequest('selling');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Description
     */
    public function getRequestDescription()
    {
        return $this->getRequest('description');
    }

    //########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request $request */
            $request = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Request_'.ucfirst($type));

            $request->setParams($this->getParams());
            $request->setListingOther($this->getListingOther());
            $request->setConfigurator($this->getConfigurator());

            $this->requests[$type] = $request;
        }

        return $this->requests[$type];
    }

    //########################################
}