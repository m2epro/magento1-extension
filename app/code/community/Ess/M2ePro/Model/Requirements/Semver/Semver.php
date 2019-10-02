<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Ess_M2ePro_Model_Requirements_Semver_Constraint_Constraint as Constraint;
use Ess_M2ePro_Model_Requirements_Semver_Comparator as Comparator;
use Ess_M2ePro_Model_Requirements_Semver_VersionParser as VersionParser;

class Ess_M2ePro_Model_Requirements_Semver_Semver
{
    const SORT_ASC = 1;
    const SORT_DESC = -1;

    /** @var VersionParser */
    private static $_versionParser;

    /**
     * Determine if given version satisfies given constraints.
     *
     * @param string $version
     * @param string $constraints
     *
     * @return bool
     */
    public static function satisfies($version, $constraints)
    {
        if (null === self::$_versionParser) {
            self::$_versionParser = new VersionParser();
        }

        $versionParser = self::$_versionParser;
        $provider = new Constraint('==', $versionParser->normalize($version));
        $constraints = $versionParser->parseConstraints($constraints);

        return $constraints->matches($provider);
    }

    /**
     * Return all versions that satisfy given constraints.
     *
     * @param array $versions
     * @param string $constraints
     *
     * @return array
     */
    public static function satisfiedBy(array $versions, $constraints)
    {
        $versions = array_filter(
            $versions, function ($version) use ($constraints) {
            return Ess_M2ePro_Model_Requirements_Semver_Semver::satisfies($version, $constraints);
            }
        );

        return array_values($versions);
    }

    /**
     * Sort given array of versions.
     *
     * @param array $versions
     *
     * @return array
     */
    public static function sort(array $versions)
    {
        return self::usort($versions, self::SORT_ASC);
    }

    /**
     * Sort given array of versions in reverse.
     *
     * @param array $versions
     *
     * @return array
     */
    public static function rsort(array $versions)
    {
        return self::usort($versions, self::SORT_DESC);
    }

    /**
     * @param array $versions
     * @param int $direction
     *
     * @return array
     */
    private static function usort(array $versions, $direction)
    {
        if (null === self::$_versionParser) {
            self::$_versionParser = new VersionParser();
        }

        $versionParser = self::$_versionParser;
        $normalized = array();

        // Normalize outside of usort() scope for minor performance increase.
        // Creates an array of arrays: [[normalized, key], ...]
        foreach ($versions as $key => $version) {
            $normalized[] = array($versionParser->normalize($version), $key);
        }

        usort(
            $normalized, function (array $left, array $right) use ($direction) {
            if ($left[0] === $right[0]) {
                return 0;
            }

            if (Comparator::lessThan($left[0], $right[0])) {
                return -$direction;
            }

            return $direction;
            }
        );

        // Recreate input array, using the original indexes which are now in sorted order.
        $sorted = array();
        foreach ($normalized as $item) {
            $sorted[] = $versions[$item[1]];
        }

        return $sorted;
    }
}