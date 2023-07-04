<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Configuration extends Mage_Core_Helper_Abstract
{
    const UPLOAD_IMAGES_MODE_AUTO = 1;
    const UPLOAD_IMAGES_MODE_SELF = 2;
    const UPLOAD_IMAGES_MODE_EPS  = 3;

    const CONFIG_GROUP = '/ebay/configuration/';

    //########################################

    public function getFeedbackNotificationMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'feedback_notification_mode'
        );
    }

    public function isEnableFeedbackNotificationMode()
    {
        return $this->getFeedbackNotificationMode() == 1;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFeedbackNotificationLastCheck($value)
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            self::CONFIG_GROUP,
            'feedback_notification_last_check',
            $value
        );

        return $this;
    }

    public function getFeedbackNotificationLastCheck()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'feedback_notification_last_check'
        );
    }

    public function getPreventItemDuplicatesMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'prevent_item_duplicates_mode'
        );
    }

    public function isEnablePreventItemDuplicatesMode()
    {
        return $this->getPreventItemDuplicatesMode() == 1;
    }

    public function getUploadImagesMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'upload_images_mode'
        );
    }

    public function isAutoUploadImagesMode()
    {
        return $this->getUploadImagesMode() == self::UPLOAD_IMAGES_MODE_AUTO;
    }

    public function isSelfUploadImagesMode()
    {
        return $this->getUploadImagesMode() == self::UPLOAD_IMAGES_MODE_SELF;
    }

    public function isEpsUploadImagesMode()
    {
        return $this->getUploadImagesMode() == self::UPLOAD_IMAGES_MODE_EPS;
    }

    public function getUkEpidsAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'uk_epids_attribute'
        );
    }

    public function getDeEpidsAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'de_epids_attribute'
        );
    }

    public function getItEpidsAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'it_epids_attribute'
        );
    }

    public function getMotorsEpidsAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'motors_epids_attribute'
        );
    }

    public function getKTypesAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'ktypes_attribute'
        );
    }

    //########################################

    public function getViewTemplateSellingFormatShowTaxCategory()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'view_template_selling_format_show_tax_category'
        );
    }

    public function getVariationMpnCanBeChanged()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'variation_mpn_can_be_changed'
        );
    }

    public function getIgnoreVariationMpnInResolver()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP,
            'ignore_variation_mpn_in_resolver'
        );
    }

    //########################################

    /**
     * @param array $values
     *
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setConfigValues(array $values)
    {
        if (isset($values['feedback_notification_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'feedback_notification_mode',
                $values['feedback_notification_mode']
            );
        }

        if (isset($values['feedback_notification_last_check'])) {
            $this->setFeedbackNotificationLastCheck($values['feedback_notification_last_check']);
        }

        if (isset($values['prevent_item_duplicates_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'prevent_item_duplicates_mode',
                $values['prevent_item_duplicates_mode']
            );
        }

        if (isset($values['upload_images_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'upload_images_mode',
                $values['upload_images_mode']
            );
        }

        //----------------------------------------

        $motorsAttributes = array();

        if (isset($values['uk_epids_attribute'])) {
            $motorsAttributes[] = $values['uk_epids_attribute'];
        }

        if (isset($values['de_epids_attribute'])) {
            $motorsAttributes[] = $values['de_epids_attribute'];
        }

        if (isset($values['it_epids_attribute'])) {
            $motorsAttributes[] = $values['it_epids_attribute'];
        }

        if (isset($values['motors_epids_attribute'])) {
            $motorsAttributes[] = $values['motors_epids_attribute'];
        }

        if (isset($values['ktypes_attribute'])) {
            $motorsAttributes[] = $values['ktypes_attribute'];
        }

        if (count(array_filter($motorsAttributes)) != count(array_unique(array_filter($motorsAttributes)))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Motors Attributes can not be the same.');
        }

        if (isset($values['uk_epids_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'uk_epids_attribute', $values['uk_epids_attribute']
            );
        }

        if (isset($values['de_epids_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'de_epids_attribute', $values['de_epids_attribute']
            );
        }

        if (isset($values['it_epids_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'it_epids_attribute', $values['it_epids_attribute']
            );
        }

        if (isset($values['motors_epids_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'motors_epids_attribute', $values['motors_epids_attribute']
            );
        }

        if (isset($values['ktypes_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'ktypes_attribute', $values['ktypes_attribute']
            );
        }
    }

    //########################################
}
