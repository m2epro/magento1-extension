<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Protocol
{
    const API_VERSION = 1;

    const MESSAGE_TEXT_KEY   = 'text';
    const MESSAGE_TYPE_KEY   = 'type';
    const MESSAGE_SENDER_KEY = 'sender';
    const MESSAGE_CODE_KEY   = 'code';

    const MESSAGE_TYPE_ERROR   = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_NOTICE  = 'notice';

    const MESSAGE_SENDER_SYSTEM    = 'system';
    const MESSAGE_SENDER_COMPONENT = 'component';

    //########################################

    protected $request = array();
    protected $requestExtraData = array();

    protected $response = array();
    protected $responseInfo = array();
    protected $messages = array();
    protected $resultType = self::MESSAGE_TYPE_ERROR;

    //########################################

    protected function sendRequest()
    {
        $requestInfo = $this->getRequestInfo();
        $requestData = $this->getRequestData();

        !is_array($requestData) && $requestData = array();
        $requestData = array_merge($requestData,$this->requestExtraData);

        $this->request = array(
            'api_version' => self::API_VERSION,
            'request' => $requestInfo,
            'data' => $requestData
        );

        $this->request['request'] = @json_encode($this->request['request']);
        $this->request['data'] = @json_encode($this->request['data']);

        $this->response = NULL;

        try {
            $curlResult = Mage::helper('M2ePro/Server')
                                    ->sendRequest($this->request,
                                                  $this->getRequestHeaders(),
                                                  $this->getRequestTimeout(),
                                                  false);

            $this->response = $curlResult['response'];
            $this->responseInfo = $curlResult;

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Client')->updateMySqlConnection();
            throw $exception;
        }

        Mage::helper('M2ePro/Client')->updateMySqlConnection();

        $this->response = @json_decode($this->response,true);

        if (!$this->isResponseValid($this->response)) {

            Mage::helper('M2ePro/Server')->switchEndpoint();

            $errorMsg = 'The Action was not completed because connection with M2E Pro Server was not set.
            There are several possible reasons:  temporary connection problem – please wait and try again later;
            block of outgoing connection by firewall – please, ensure that connection to s1.m2epro.com and
            s2.m2epro.com, port 443 is allowed; CURL library is not installed or it does not support HTTPS Protocol –
            please, install/update CURL library on your server and ensure it supports HTTPS Protocol.
            More information you can find <a target="_blank" href="'.
                Mage::helper('M2ePro/Module_Support')
                    ->getKnowledgebaseUrl('664870-issues-with-m2e-pro-server-connection')
                .'">here</a>';

            throw new Ess_M2ePro_Model_Exception_Connection($errorMsg, $this->responseInfo);
        }

        $this->processResponseInfo($this->response['response']);

        return $this->response['data'];
    }

    protected function isResponseValid($response)
    {
        return isset($response['response']) && isset($response['data']);
    }

    protected function processResponseInfo($responseInfo)
    {
        $this->resultType = $responseInfo['result']['type'];

        $internalServerErrorMessage = array();
        $isMaintenanceModeEnabled = false;

        foreach ($responseInfo['result']['messages'] as $message) {

            if ($this->isMessageError($message) && $this->isMessageSenderSystem($message)) {
                $internalServerErrorMessage[] = $message[self::MESSAGE_TEXT_KEY];

                if ((int)$message[self::MESSAGE_CODE_KEY] == 3) {
                    $isMaintenanceModeEnabled = true;
                }

                continue;
            }

            $this->messages[] = $message;
        }

        if (!empty($internalServerErrorMessage)) {

            throw new Ess_M2ePro_Model_Exception(Mage::helper('M2ePro')->__(
                "Internal Server Error(s) [%error_message%]", implode(', ', $internalServerErrorMessage)
            ),array(),0,!$isMaintenanceModeEnabled);
        }
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getRequestHeaders()
    {
        $commandTemp = $this->getCommand();

        if (!is_array($commandTemp) || !isset($commandTemp[0]) ||
            !isset($commandTemp[1]) || !isset($commandTemp[2])) {
            throw new Ess_M2ePro_Model_Exception('Requested Command has invalid format.');
        }

        return array(
            'M2EPRO-API-VERSION: '.self::API_VERSION,
            'M2EPRO-API-COMPONENT: '.(string)$this->getComponent(),
            'M2EPRO-API-COMPONENT-VERSION: '.(int)$this->getComponentVersion(),
            'M2EPRO-API-COMMAND: /'.$commandTemp[0] .'/'.$commandTemp[1].'/'.$commandTemp[2].'/'
        );
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return 300;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getRequestInfo()
    {
        $commandTemp = $this->getCommand();

        if (!is_array($commandTemp) || !isset($commandTemp[0]) ||
            !isset($commandTemp[1]) || !isset($commandTemp[2])) {
            throw new Ess_M2ePro_Model_Exception('Requested Command has invalid format.');
        }

        $request = array(
            'mode' => Mage::helper('M2ePro/Module')->isDevelopmentEnvironment() ? 'development' : 'production',
            'client' => array(
                'platform' => array(
                    'name' => Mage::helper('M2ePro/Magento')->getName().
                                ' ('.Mage::helper('M2ePro/Magento')->getEditionName().')',
                    'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
                ),
                'module' => array(
                    'name' => Mage::helper('M2ePro/Module')->getName(),
                    'version' => Mage::helper('M2ePro/Module')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Module')->getRevision()
                ),
                'location' => array(
                    'domain' => Mage::helper('M2ePro/Client')->getDomain(),
                    'ip' => Mage::helper('M2ePro/Client')->getIp(),
                    'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
                ),
                'locale' => Mage::helper('M2ePro/Magento')->getLocale()
            ),
            'auth' => array(),
            'component' => array(
                'name' => (string)$this->getComponent(),
                'version' => (int)$this->getComponentVersion()
            ),
            'command' => array(
                'entity' => $commandTemp[0],
                'type' => $commandTemp[1],
                'name' => $commandTemp[2]
            )
        );

        $adminKey = Mage::helper('M2ePro/Server')->getAdminKey();
        !is_null($adminKey) && $adminKey != '' && $request['auth']['admin_key'] = $adminKey;

        $applicationKey = Mage::helper('M2ePro/Server')->getApplicationKey();
        !is_null($applicationKey) && $applicationKey != '' && $request['auth']['application_key'] = $applicationKey;

        $licenseKey = Mage::helper('M2ePro/Module_License')->getKey();
        !is_null($licenseKey) && $licenseKey != '' && $request['auth']['license_key'] = $licenseKey;

        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();
        !is_null($installationKey) && $installationKey != '' && $request['auth']['installation_key'] = $installationKey;

        return $request;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function getRequestData();

    /**
     * @return array
     */
    public function getRequestDataPackage()
    {
        return array(
            'info' => $this->getRequestInfo(),
            'data' => $this->getRequestData()
        );
    }

    //########################################

    /**
     * @abstract
     * @return string
     */
    abstract protected function getComponent();

    /**
     * @abstract
     * @return int
     */
    abstract protected function getComponentVersion();

    // ---------------------------------------

    /**
     * @abstract
     * @return array
     */
    abstract protected function getCommand();

    //########################################

    protected function printDebugData()
    {
        if (!Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return;
        }

        if (count($this->request) > 0) {
            echo '<h1>Request:</h1>',
            '<pre>';
            var_dump($this->request);
            echo '</pre>';
        }

        if (count($this->response) > 0) {
            echo '<h1>Response:</h1>',
            '<pre>';
            var_dump($this->response);
            echo '</pre>';
        }
    }

    //########################################

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageError($message)
    {
        return $message[self::MESSAGE_TYPE_KEY] == self::MESSAGE_TYPE_ERROR;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageWarning($message)
    {
        return $message[self::MESSAGE_TYPE_KEY] == self::MESSAGE_TYPE_WARNING;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageSenderSystem($message)
    {
        return $message[self::MESSAGE_SENDER_KEY] == self::MESSAGE_SENDER_SYSTEM;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageSenderComponent($message)
    {
        return $message[self::MESSAGE_SENDER_KEY] == self::MESSAGE_SENDER_COMPONENT;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = array();

        foreach ($this->messages as $message) {
            $this->isMessageError($message) && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        $messages = array();

        foreach ($this->messages as $message) {
            $this->isMessageWarning($message) && $messages[] = $message;
        }

        return $messages;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasErrorMessages()
    {
        return count($this->getErrorMessages()) > 0;
    }

    /**
     * @return bool
     */
    public function hasWarningMessages()
    {
        return count($this->getWarningMessages()) > 0;
    }

    // ---------------------------------------

    /**
     * @return null|string
     */
    public function getCombinedErrorMessage()
    {
        $messages = array();

        foreach ($this->getErrorMessages() as $message) {
            $messages[] = $message[self::MESSAGE_TEXT_KEY];
        }

        return !empty($messages) ? implode(', ', $messages) : null;
    }

    //########################################
}