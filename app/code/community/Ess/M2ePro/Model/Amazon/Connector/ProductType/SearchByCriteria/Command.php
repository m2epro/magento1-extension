<?php

class Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Command
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    const REQUEST_PARAM_KEY = 'request_param';

    /** @var Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response */
    private $preparedResponse;

    protected function getCommand()
    {
        return array('category', 'search', 'byCriteria');
    }

    public function getRequestData()
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Request $request */
        $request = $this->_params[self::REQUEST_PARAM_KEY];

        return array(
            'marketplace_id' => $request->getMarketplaceId(),
            'criteria' => $request->getCriteria(),
        );
    }

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();
        $response = new Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response();

        foreach ($responseData['categories'] as $category) {
            $response->addCategory(
                $category['name'],
                $category['is_leaf'],
                $category['product_types']
            );
        }

        $this->preparedResponse = $response;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response
     */
    public function getPreparedResponse()
    {
        return $this->preparedResponse;
    }
}