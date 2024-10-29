<?php

/**
 * @method Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response getResponseData()
 */
class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Command
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    const PARAM_KEY_MARKETPLACE_ID = 'marketplace_id';
    const PARAM_KEY_PART_NUMBER = 'part_number';

    protected function getCommand()
    {
        return array('marketplace', 'get', 'categories');
    }

    public function getRequestData()
    {
        return array(
            'marketplace' => $this->_params[self::PARAM_KEY_MARKETPLACE_ID],
            'part_number' => $this->_params[self::PARAM_KEY_PART_NUMBER],
        );
    }

    protected function prepareResponseData()
    {
        $categories = array();

        $response = $this->getResponse()->getData();
        foreach ($response['categories'] as $responseCategory) {
            $category = new Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category(
                $responseCategory['id'],
                $responseCategory['parent_id'],
                $responseCategory['title'],
                $responseCategory['is_leaf']
            );

            if ($responseCategory['is_leaf']) {
                $category->setProductType(
                    new Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category_ProductType(
                        $responseCategory['product_type']['title'],
                        $responseCategory['product_type']['nick']
                    )
                );
            }

            $categories[] = $category;
        }

        $part = new Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part(
            $response['total_parts'],
            $response['next_part']
        );

        $this->_responseData = new Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response(
            $categories,
            $part
        );
    }

    protected function validateResponse()
    {
        $response = $this->getResponse()->getData();

        return isset($response['categories'])
            && isset($response['total_parts'])
            && array_key_exists('next_part', $response);
    }
}
