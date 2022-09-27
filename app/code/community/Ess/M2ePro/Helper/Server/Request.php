<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Server_Request extends Mage_Core_Helper_Abstract
{
    //########################################

    public function single(
        array $package,
        $serverBaseUrl = null,
        $serverHostName = null,
        $tryToResendOnError = true,
        $tryToSwitchEndpointOnError = true,
        $canIgnoreMaintenance = false
    ) {
        if (!$canIgnoreMaintenance && Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            throw new Ess_M2ePro_Model_Exception_Connection(
                'The action is temporarily unavailable. M2E Pro Server is under maintenance. Please try again later.'
            );
        }

        !$serverBaseUrl  && $serverBaseUrl  = $this->getServerHelper()->getEndpoint();
        !$serverHostName && $serverHostName = $this->getServerHelper()->getCurrentHostName();

        $curlObject = $this->buildCurlObject($package, $serverBaseUrl, $serverHostName);
        $responseBody = curl_exec($curlObject);

        $response = array(
            'body'               => $responseBody,
            'curl_error_number'  => curl_errno($curlObject),
            'curl_error_message' => curl_error($curlObject),
            'curl_info'          => curl_getinfo($curlObject)
        );

        curl_close($curlObject);

        if ($response['body'] === false) {
            $switchingResult = false;
            $tryToSwitchEndpointOnError && $switchingResult = $this->getServerHelper()->switchEndpoint();

            Mage::helper('M2ePro/Module_Logger')->process(
                array(
                    'curl_error_number' => $response['curl_error_number'],
                    'curl_error_message' => $response['curl_error_message'],
                    'curl_info' => $response['curl_info']
                ),
                'Curl Empty Response'
            );

            if ($this->canRepeatRequest(
                $response['curl_error_number'],
                $tryToResendOnError,
                $tryToSwitchEndpointOnError,
                $switchingResult
            )) {
                return $this->single(
                    $package,
                    $tryToSwitchEndpointOnError ? $this->getServerHelper()->getEndpoint() : $serverBaseUrl,
                    $tryToSwitchEndpointOnError ? $this->getServerHelper()->getCurrentHostName() : $serverHostName,
                    false,
                    $tryToSwitchEndpointOnError,
                    $canIgnoreMaintenance
                );
            }

            throw new Ess_M2ePro_Model_Exception_Connection(
                Mage::helper('M2ePro')->__(
                    'M2E Pro Server connection failed. Find the solution <a target="_blank" href="%url%">here</a>',
                    Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/articles/9000200887')
                ),
                array(
                    'curl_error_number'  => $response['curl_error_number'],
                    'curl_error_message' => $response['curl_error_message'],
                    'curl_info'          => $response['curl_info']
                )
            );
        }

        return $response;
    }

    public function multiple(
        array $packages,
        $serverBaseUrl = null,
        $serverHostName = null,
        $tryToResendOnError = true,
        $tryToSwitchEndpointOnError = true,
        $asynchronous = false,
        $canIgnoreMaintenance = false
    ) {
        if (!$canIgnoreMaintenance && Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            throw new Ess_M2ePro_Model_Exception_Connection(
                'The action is temporarily unavailable. M2E Pro Server is under maintenance. Please try again later.'
            );
        }

        if (empty($packages)) {
            throw new Ess_M2ePro_Model_Exception_Logic("Packages is empty.");
        }

        !$serverBaseUrl  && $serverBaseUrl  = $this->getServerHelper()->getEndpoint();
        !$serverHostName && $serverHostName = $this->getServerHelper()->getCurrentHostName();

        $responses = array();

        if (count($packages) == 1 || !$asynchronous) {
            foreach ($packages as $key => $package) {
                $curlObject = $this->buildCurlObject($package, $serverBaseUrl, $serverHostName);
                $responseBody = curl_exec($curlObject);

                $responses[$key] = array(
                    'body'               => $responseBody,
                    'curl_error_number'  => curl_errno($curlObject),
                    'curl_error_message' => curl_error($curlObject),
                    'curl_info'          => curl_getinfo($curlObject)
                );

                curl_close($curlObject);
            }
        } else {
            $curlObjectsPool = array();
            $multiCurlObject = curl_multi_init();

            foreach ($packages as $key => $package) {
                $curlObjectsPool[$key] = $this->buildCurlObject($package, $serverBaseUrl, $serverHostName);
                curl_multi_add_handle($multiCurlObject, $curlObjectsPool[$key]);
            }

            do {
                curl_multi_exec($multiCurlObject, $stillRunning);

                if ($stillRunning) {
                    curl_multi_select($multiCurlObject, 1); //sleep in sec.
                }
            } while ($stillRunning > 0);

            foreach ($curlObjectsPool as $key => $curlObject) {
                $responses[$key] = array(
                    'body'               => curl_multi_getcontent($curlObject),
                    'curl_error_number'  => curl_errno($curlObject),
                    'curl_error_message' => curl_error($curlObject),
                    'curl_info'          => curl_getinfo($curlObject)
                );

                curl_multi_remove_handle($multiCurlObject, $curlObject);
                curl_close($curlObject);
            }

            curl_multi_close($multiCurlObject);
        }

        $isResponseFailed = false;
        $switchingResult = false;

        foreach ($responses as $key => $response) {
            if ($response['body'] === false) {
                $isResponseFailed = true;
                $tryToSwitchEndpointOnError && $switchingResult = $this->getServerHelper()->switchEndpoint();

                Mage::helper('M2ePro/Module_Logger')->process(
                    array(
                        'curl_error_number'  => $response['curl_error_number'],
                        'curl_error_message' => $response['curl_error_message'],
                        'curl_info'          => $response['curl_info']
                    ),
                    'Curl Empty Response'
                );
                break;
            }
        }

        if ($tryToResendOnError && $isResponseFailed) {
            $failedRequests = array();

            foreach ($responses as $key => $response) {
                if ($response['body'] === false) {
                    if ($this->canRepeatRequest(
                        $response['curl_error_number'],
                        $tryToResendOnError,
                        $tryToSwitchEndpointOnError,
                        $switchingResult
                    )) {
                        $failedRequests[$key] = $packages[$key];
                    }
                }
            }

            if (!empty($failedRequests)) {
                $secondAttemptResponses = $this->multiple(
                    $failedRequests,
                    $tryToSwitchEndpointOnError ? $this->getServerHelper()->getEndpoint() : $serverBaseUrl,
                    $tryToSwitchEndpointOnError ? $this->getServerHelper()->getCurrentHostName() : $serverHostName,
                    false,
                    $tryToSwitchEndpointOnError,
                    $asynchronous,
                    $canIgnoreMaintenance
                );

                $responses = array_merge($responses, $secondAttemptResponses);
            }
        }

        return $responses;
    }

    // ----------------------------------------

    protected function buildCurlObject(
        $package,
        $serverBaseUrl,
        $serverHostName
    ) {
        $curlObject = curl_init();

        $preparedHeaders = array();
        $serverHostName && $preparedHeaders[] = 'Host:'. $serverHostName;

        if (!empty($package['headers'])) {
            foreach ($package['headers'] as $header) {
                $preparedHeaders[] = $header;
            }
        }

        $postData = array();
        if (!empty($package['data'])) {
            $postData = $package['data'];
        }

        $timeout = 300;
        if (isset($package['timeout'])) {
            $timeout = (int) $package['timeout'];
        }

        $sslVerifyPeer = true;
        $sslVerifyHost = 2;

        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $serverBaseUrl)) {
            $sslVerifyPeer = false;
            $sslVerifyHost = false;
        }

        curl_setopt_array(
            $curlObject,
            array(
                // set the server we are using
                CURLOPT_URL => $serverBaseUrl,

                CURLOPT_SSL_VERIFYPEER => $sslVerifyPeer,
                CURLOPT_SSL_VERIFYHOST => $sslVerifyHost,

                // disable http headers
                CURLOPT_HEADER => false,

                // set the headers using the array of headers
                CURLOPT_HTTPHEADER => $preparedHeaders,

                // set the data body of the request
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData, '', '&'),

                // set it to return the transfer as a string from curl_exec
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => $timeout
            )
        );

        return $curlObject;
    }

    protected function canRepeatRequest(
        $curlErrorNumber,
        $tryToResendOnError,
        $tryToSwitchEndpointOnError,
        $switchingResult
    ) {
        return $curlErrorNumber !== CURLE_OPERATION_TIMEOUTED && $tryToResendOnError &&
               (!$tryToSwitchEndpointOnError || ($tryToSwitchEndpointOnError && $switchingResult));
    }

    //########################################

    /**
     * @return Ess_M2ePro_Helper_Server
     */
    protected function getServerHelper()
    {
        return Mage::helper('M2ePro/Server');
    }

    //########################################
}
