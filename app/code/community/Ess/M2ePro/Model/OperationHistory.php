<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_OperationHistory extends Ess_M2ePro_Model_Abstract
{
    const MAX_LIFETIME_INTERVAL = 864000; // 10 days

    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    private $object = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/OperationHistory');
    }

    //########################################

    public function setObject($value)
    {
        if (is_object($value)) {
            $this->object = $value;
        } else {
            $this->object = Mage::getModel('M2ePro/OperationHistory')->load($value);
            !$this->object->getId() && $this->object = NULL;
        }

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getObject()
    {
        return $this->object;
    }

    //########################################

    public function start($nick, $parentId = NULL, $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $data = array(
            'nick' => $nick,
            'parent_id' => $parentId,
            'initiator' => $initiator,
            'start_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        $this->object = Mage::getModel('M2ePro/OperationHistory')->setData($data)->save();

        return true;
    }

    public function stop()
    {
        if (is_null($this->object) || $this->object->getData('end_date')) {
            return false;
        }

        $this->object->setData('end_date',Mage::helper('M2ePro')->getCurrentGmtDate())->save();

        return true;
    }

    //########################################

    public function setContentData($key, $value)
    {
        if (is_null($this->object)) {
            return false;
        }

        $data = array();
        if ($this->object->getData('data') != '') {
            $data = json_decode($this->object->getData('data'),true);
        }

        $data[$key] = $value;
        $this->object->setData('data',json_encode($data))->save();

        return true;
    }

    public function getContentData($key)
    {
        if (is_null($this->object)) {
            return NULL;
        }

        if ($this->object->getData('data') == '') {
            return NULL;
        }

        $data = json_decode($this->object->getData('data'),true);

        if (isset($data[$key])) {
            return $data[$key];
        }

        return NULL;
    }

    //########################################

    public function cleanOldData()
    {
        $minDate = new DateTime('now', new DateTimeZone('UTC'));
        $minDate->modify('-'.self::MAX_LIFETIME_INTERVAL.' seconds');

        Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete(
                    Mage::getSingleton('core/resource')->getTableName('m2epro_operation_history'),
                    array(
                        '`create_date` <= ?' => $minDate->format('Y-m-d H:i:s')
                    )
            );
    }

    public function makeShutdownFunction()
    {
        if (is_null($this->object)) {
            return false;
        }

        $functionCode =
            '$object = Mage::getModel(\'M2ePro/OperationHistory\');
             $object->setObject('.$this->object->getId().');

             if (!$object->stop()) {
                return;
             }

             $collection = $object->getCollection()
                     ->addFieldToFilter(\'parent_id\', '.$this->object->getId().');

             if ($collection->getSize()) {
                return;
             }

             $error = error_get_last();

             if (is_null($error)) {
                 return;
             }

             if (in_array((int)$error[\'type\'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                 $stackTrace = @debug_backtrace(false);
                 $object->setContentData(\'fatal_error\',array(
                    \'message\' => $error[\'message\'],
                    \'file\' => $error[\'file\'],
                    \'line\' => $error[\'line\'],
                    \'trace\' => Mage::helper(\'M2ePro/Module_Exception\')->getFatalStackTraceInfo($stackTrace)
                 ));
             }';

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //########################################

    public function getDataInfo($nestingLevel = 0)
    {
        if (is_null($this->object)) {
            return NULL;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#',80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));

        $contentData = (array)json_decode($this->getObject()->getData('data'),true);
        $contentData = preg_replace('/^/m', "{$offset}", print_r($contentData, true));

        return <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$contentData}
{$offset}{$separationLine}

INFO;
    }

    public function getFullDataInfo($nestingLevel = 0)
    {
        if (is_null($this->object)) {
            return NULL;
        }

        $dataInfo = $this->getDataInfo($nestingLevel);

        $childObjects = $this->getCollection()
                             ->addFieldToFilter('parent_id', $this->getObject()->getId())
                             ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {

            $object = Mage::getModel('M2ePro/OperationHistory');
            $object->setObject($item);

            $dataInfo .= $object->getFullDataInfo($nestingLevel);
        }

        return $dataInfo;
    }

    // ---------------------------------------

    protected function getTotalTime()
    {
        $totalTime = strtotime($this->getObject()->getData('end_date')) -
                     strtotime($this->getObject()->getData('start_date'));

        if ($totalTime < 0) {
            return 'n/a';
        }

        $minutes = (int)($totalTime / 60);
        $minutes < 10 && $minutes = '0'.$minutes;

        $seconds = $totalTime - $minutes * 60;
        $seconds < 10 && $seconds = '0'.$seconds;

        return "{$minutes}:{$seconds}";
    }

    //########################################
}