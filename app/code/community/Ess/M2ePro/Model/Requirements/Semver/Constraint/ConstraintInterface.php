<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

interface Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface
{
    /**
     * @param Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches(Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface $provider);

    /**
     * @return string
     */
    public function getPrettyString();

    /**
     * @return string
     */
    public function __toString();
}
