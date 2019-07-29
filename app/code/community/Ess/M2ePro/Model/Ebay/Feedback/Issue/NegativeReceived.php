<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Issue_Object as Issue;

class Ess_M2ePro_Model_Ebay_Feedback_Issue_NegativeReceived extends Ess_M2ePro_Model_Issue_Locator_Abstract
{
    const CACHE_KEY = __CLASS__;

    //########################################

    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        $config = Mage::helper('M2ePro/Module')->getConfig();
        if (!$config->getGroupValue('/view/ebay/feedbacks/notification/', 'mode')) {
            return array();
        }

        $lastCheckDate = $config->getGroupValue('/view/ebay/feedbacks/notification/', 'last_check');

        if (is_null($lastCheckDate)) {
            $config->setGroupValue(
                '/view/ebay/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate()
            );
            return array();
        }

        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Feedback_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
            ->addFieldToFilter('buyer_feedback_date', array('gt' => $lastCheckDate))
            ->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Ebay_Feedback::TYPE_NEGATIVE);

        if ($collection->getSize() > 0) {

            $tempMessage = Mage::helper('M2ePro')->__(
                'New Buyer negative Feedback was received. Go to the <a href="%url%" target="blank">Feedback Page</a>.',
                Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_ebay_feedback/index')
            );

            $editHash = md5(self::CACHE_KEY . Mage::helper('M2ePro')->getCurrentGmtDate());
            $messageUrl = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_ebay_feedback/index',
                array('_query' => array('hash' => $editHash))
            );

            $config->setGroupValue(
                '/view/ebay/feedbacks/notification/', 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            return array(
                Mage::getModel('M2ePro/Issue_Object', array(
                    Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                    Issue::KEY_TITLE => Mage::helper('M2ePro')->__('New Buyer negative Feedback was received.'),
                    Issue::KEY_TEXT  => $tempMessage,
                    Issue::KEY_URL   => $messageUrl
                ))
            );
        }

        return array();
    }

    //########################################

    public function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
               Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    //########################################
}