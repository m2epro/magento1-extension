<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Images extends Mage_Core_Helper_Abstract
{
    const SHOULD_BE_URLS_SECURE_NO  = 0;
    const SHOULD_BE_URLS_SECURE_YES = 1;

    //########################################

    //todo MUST be moved from here. It affects not only eBay Description but Amazon integration also
    public function shouldBeUrlsSecure()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/description/', 'should_be_ulrs_secure'
        );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Image[] $images
     * @param string|null $attributeLabel for Variation product
     * @return string $hash
     */
    public function getHash(array $images, $attributeLabel = null)
    {
        if (empty($images)) {
            return null;
        }

        $hashes = array();
        $haveNotSelfHostedImage = false;

        foreach ($images as $image) {
            $tempImageHash = $image->getHash();

            if (!$image->isSelfHosted()) {
                $haveNotSelfHostedImage = true;
            }

            $hashes[] = $tempImageHash;
        }

        $hash = sha1(Mage::helper('M2ePro')->jsonEncode($hashes));
        $attributeLabel && $hash .= $attributeLabel;

        if ($haveNotSelfHostedImage) {
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            $hash .= '##' . $date->getTimestamp();
        }

        return $hash;
    }

    /**
     * @param string $hash
     * @param int $lifetime (in days) 2 by default
     * @return bool
     */
    public function isHashBelated($hash, $lifetime = 2)
    {
        if (strpos($hash, '##') === false) {
            return false;
        }

        $parts = explode('##', $hash);

        if (empty($parts[1])) {
            return true;
        }

        $validTill = new \DateTime('now', new \DateTimeZone('UTC'));
        $validTill->setTimestamp((int)$parts[1]);
        $validTill->modify("+ {$lifetime} days");

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $now->getTimestamp() >= $validTill->getTimestamp();
    }

    //----------------------------------------

    /**
     * @param string $savedHash
     * @param string $currentHash
     * @return bool
     */
    public function areHashesTheSame($savedHash, $currentHash)
    {
        if ($savedHash == $currentHash) {
            return true;
        }

        if (strpos($savedHash, '##') === false || strpos($currentHash, '##') === false) {
            return false;
        }

        $savedHash = explode('##', $savedHash);
        $currentHash = explode('##', $currentHash);

        return $savedHash[0] == $currentHash[0];
    }

    //########################################
}
