<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_M2ePro_Virtual extends Ess_M2ePro_Model_Connector_M2ePro_Abstract
{
    private $cache = array();

    // ########################################

    protected function getCommand()
    {
        if (isset($this->cache['command'])) {
            return $this->cache['command'];
        }

        $this->cache['command'] = $this->params['__command__'];
        unset($this->params['__command__']);

        return $this->cache['command'];
    }

    // ########################################

    protected function getRequestInfo()
    {
        if (isset($this->cache['request_info'])) {
            return $this->cache['request_info'];
        }

         if (!isset($this->params['__request_info__']) ||
            !is_array($this->params['__request_info__']) ||
            count($this->params['__request_info__']) <= 0) {
            $this->cache['request_info'] = parent::getRequestInfo();
        } else {
            $this->cache['request_info'] = $this->params['__request_info__'];
            unset($this->params['__request_info__']);
        }

        return $this->cache['request_info'];
    }

    protected function getRequestData()
    {
        if (isset($this->cache['request_data'])) {
            return $this->cache['request_data'];
        }

        $this->cache['request_data'] = $this->params['__request_data__'];
        unset($this->params['__request_data__']);

        return $this->cache['request_data'];
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if (!is_null($this->params['__response_data_key__'])) {
            if (isset($response[$this->params['__response_data_key__']])) {
                return $response[$this->params['__response_data_key__']];
            } else {
                return NULL;
            }
        }
        return $response;
    }

    // ########################################
}