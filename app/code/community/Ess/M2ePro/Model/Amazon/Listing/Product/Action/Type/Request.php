<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $_cachedData = array();

    /**
     * @var array
     */
    protected $_dataTypes = array(
        'qty',
        'price_regular',
        'price_business',
        'details'
    );

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract[]
     */
    protected $_dataBuilders = array();

    //########################################

    public function setCachedData(array $data)
    {
        $this->_cachedData = $data;
    }

    /**
     * @return array
     */
    public function getCachedData()
    {
        return $this->_cachedData;
    }

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $this->beforeBuildDataEvent();
        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectDataBuildersWarningMessages();

        return $data;
    }

    //########################################

    protected function beforeBuildDataEvent()
    {
        return null;
    }

    abstract protected function getActionData();

    // ---------------------------------------

    protected function prepareFinalData(array $data)
    {
        return $data;
    }

    protected function collectDataBuildersWarningMessages()
    {
        foreach ($this->_dataTypes as $requestType) {
            $messages = $this->getDataBuilder($requestType)->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    //########################################

    /**
     * @return array
     */
    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return array();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return array();
        }


        $dataBuilder = $this->getDataBuilder('qty');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getRegularPriceData()
    {
        if (!$this->getConfigurator()->isRegularPriceAllowed()) {
            return array();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('price_regular');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getBusinessPriceData()
    {
        if (!$this->getConfigurator()->isBusinessPriceAllowed()) {
            return array();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('price_business');
        return $dataBuilder->getData();
    }

    /**
     * @return array
     */
    public function getDetailsData()
    {
        if (!$this->getConfigurator()->isDetailsAllowed()) {
            return array();
        }

        $dataBuilder = $this->getDataBuilder('details');
        $data = $dataBuilder->getData();

        $this->addMetaData('details_data', $data);

        return $data;
    }

    //########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
     */
    protected function getDataBuilder($type)
    {
        if (!isset($this->_dataBuilders[$type])) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract $dataBuilder */
            $dataBuilder = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_DataBuilder_'.ucfirst($type));

            $dataBuilder->setParams($this->getParams());
            $dataBuilder->setListingProduct($this->getListingProduct());
            $dataBuilder->setCachedData($this->getCachedData());

            $this->_dataBuilders[$type] = $dataBuilder;
        }

        return $this->_dataBuilders[$type];
    }

    //########################################
}
