<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Server_Request extends Mage_Core_Helper_Abstract
{
    //----------------------------------------

    public function single(
        array $package,
        $canIgnoreMaintenance = false
    ) {
        if (!$canIgnoreMaintenance && Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            throw new Ess_M2ePro_Model_Exception_Connection(
                'The action is temporarily unavailable. M2E Pro Server is under maintenance. Please try again later.'
            );
        }

        $curlObject = $this->buildCurlObject($package, $this->getServerHelper()->getEndpoint());
        $responseBody = curl_exec($curlObject);

        $response = array(
            'body'               => $responseBody,
            'curl_error_number'  => curl_errno($curlObject),
            'curl_error_message' => curl_error($curlObject),
            'curl_info'          => curl_getinfo($curlObject)
        );

        curl_close($curlObject);

        if ($response['body'] === false) {
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

        $serverHost = $this->getServerHelper()->getEndpoint();

        $responses = array();

        if (count($packages) == 1 || !$asynchronous) {
            foreach ($packages as $key => $package) {
                $curlObject = $this->buildCurlObject($package, $serverHost);
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
                $curlObjectsPool[$key] = $this->buildCurlObject($package, $serverHost);
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

        foreach ($responses as $response) {
            if ($response['body'] === false) {
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

        return $responses;
    }

    //----------------------------------------

    protected function buildCurlObject(
        $package,
        $serverHost
    ) {
        $curlObject = curl_init();

        $preparedHeaders = array();
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

        curl_setopt_array(
            $curlObject,
            array(
                // set the server we are using
                CURLOPT_URL => $serverHost,

                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,

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

    //----------------------------------------

    /**
     * @return Ess_M2ePro_Helper_Server
     */
    protected function getServerHelper()
    {
        return Mage::helper('M2ePro/Server');
    }

    //----------------------------------------
}
