<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Tests\Unit\Version;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Version\InvalidVersionException;
use PrestaShop\Module\PrestashopCheckout\Version\Version;

class VersionTest extends TestCase
{
    /**
     * @var Version
     */
    protected $version;

    /**
     * @var Version
     */
    protected $anotherVersion;

    const VERSION = '6.3.1.0';
    const PREFIX_VERSION = 6;
    const MAJOR_VERSION = 3;
    const MINOR_VERSION = 1;
    const RELEASE_VERSION = 0;

    const ANOTHER_VERSION = '7.3.1.0';
    const ANOTHER_PREFIX_VERSION = 7;
    const ANOTHER_MAJOR_VERSION = 3;
    const ANOTHER_MINOR_VERSION = 1;
    const ANOTHER_RELEASE_VERSION = 0;

    protected function setUp()
    {
        $this->version = new Version(
            self::VERSION,
            self::PREFIX_VERSION,
            self::MAJOR_VERSION,
            self::MINOR_VERSION,
            self::RELEASE_VERSION
        );

        $this->anotherVersion = new Version(
            self::ANOTHER_VERSION,
            self::PREFIX_VERSION,
            self::ANOTHER_MAJOR_VERSION,
            self::ANOTHER_MINOR_VERSION,
            self::ANOTHER_RELEASE_VERSION
        );
    }

    public function testGetVersion()
    {
        $this->assertSame(self::VERSION, $this->version->getVersion());
    }

    public function testGetPrefixVersion()
    {
        $this->assertSame(self::PREFIX_VERSION, $this->version->getPrefixVersion());
    }

    public function testGetMajorVersion()
    {
        $this->assertSame(self::MAJOR_VERSION, $this->version->getMajorVersion());
    }

    public function testGetMinorVersion()
    {
        $this->assertSame(self::MINOR_VERSION, $this->version->getMinorVersion());
    }

    public function testGetReleaseVersion()
    {
        $this->assertSame(self::RELEASE_VERSION, $this->version->getPatchVersion());
    }

    /**
     * @dataProvider provideVersions
     *
     * @param string $string
     * @param array $expected
     */
    public function testBuildFromString($string, array $expected)
    {
        $version = Version::buildFromString($string);

        $this->assertSame($expected['version'], $version->getVersion(), 'Version string is incorrect');
        $this->assertSame($expected['fullVersion'], $version->getVersion(true), 'Full version string is incorrect');
        $this->assertSame($expected['semVersion'], $version->getSemVersion(), 'Semantic version string is incorrect');
        $this->assertSame($expected['prefix'], $version->getPrefixVersion(), 'Prefix version is incorrect');
        $this->assertSame($expected['major'], $version->getMajorVersion(), 'Major version is incorrect');
        $this->assertSame($expected['minor'], $version->getMinorVersion(), 'Minor version is incorrect');
        $this->assertSame($expected['patch'], $version->getPatchVersion(), 'Patch version is incorrect');
        $this->assertSame($expected['preRelease'], $version->getPreReleaseVersion(), 'Pre release version is incorrect');
        $this->assertSame($expected['buildMeta'], $version->getBuildMetadata(), 'Build metadata is incorrect');
    }

    /**
     * @dataProvider getCompareGreater
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareGreaterVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isGreaterThan($version),
            sprintf('Failed to assert that %s %s greater than %s', $this->version, $this->getVerb($result), $version)
        );
    }

    /**
     * @dataProvider getCompareGreaterEqual
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareGreaterEqualVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isGreaterThanOrEqualTo($version),
            sprintf(
                'Failed to assert that %s %s greater or equal to %s',
                $this->version,
                $this->getVerb($result),
                $version
            )
        );
    }

    /**
     * @dataProvider getCompareLess
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareLessVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isLessThan($version),
            sprintf(
                'Failed to assert that %s %s less than %s',
                $this->version,
                $this->getVerb($result),
                $version
            )
        );
    }

    /**
     * @dataProvider getAnotherCompareGreater
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareGreaterAnotherVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->anotherVersion->isGreaterThan($version),
            sprintf(
                'Failed to assert that %s %s greater than %s',
                $this->anotherVersion,
                $this->getVerb($result),
                $version
            )
        );
    }

    /**
     * @dataProvider getCompareLessEqual
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareLessEqualVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isLessThanOrEqualTo($version),
            sprintf(
                'Failed to assert that %s %s less or equal to %s',
                $this->version,
                $this->getVerb($result),
                $version
            )
        );
    }

    /**
     * @dataProvider getCompareEqual
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareEqualVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isEqualTo($version),
            sprintf(
                'Failed to assert that %s %s equal to %s',
                $this->version,
                $this->getVerb($result),
                $version
            )
        );
    }

    /**
     * @dataProvider getCompareNotEqual
     *
     * @param string $version Version
     * @param bool $result Result
     */
    public function testCompareNotEqualVersion($version, $result)
    {
        $this->assertEquals(
            $result,
            $this->version->isNotEqualTo($version),
            sprintf(
                'Failed to assert that %s %s equal to %s',
                $this->version,
                $this->getVerb(!$result),
                $version
            )
        );
    }

    /**
     * @dataProvider getTwoVersionsToCompare
     *
     * @param string $first Version
     * @param string $second Version
     * @param string $expectedComparison Comparison character
     *
     * @throws InvalidVersionException
     */
    public function testCompareTwoVersions($first, $second, $expectedComparison)
    {
        $firstVersion = Version::buildFromString($first);
        $secondVersion = Version::buildFromString($second);

        if ($expectedComparison === '<') {
            $this->assertTrue(
                $firstVersion->isLessThan($secondVersion),
                sprintf(
                    'Failed to assert that %s is less than %s',
                    $firstVersion,
                    $secondVersion
                )
            );
        } elseif ($expectedComparison === '>') {
            $this->assertTrue(
                $firstVersion->isGreaterThan($secondVersion),
                sprintf(
                    'Failed to assert that %s is greater than %s',
                    $firstVersion,
                    $secondVersion
                )
            );
        } else {
            $this->assertTrue(
                $firstVersion->isEqualTo($secondVersion),
                sprintf(
                    'Failed to assert that %s is equal to %s',
                    $firstVersion,
                    $secondVersion
                )
            );
        }
    }

    /**
     * @dataProvider getInvalidVersions
     *
     * @param string $version Version
     */
    public function testCheckInvalidVersion($version)
    {
        $this->expectException(InvalidVersionException::class);
        $this->version->isLessThan($version);
    }

    /**
     * @return array[]
     */
    public function provideVersions()
    {
        return [
            '6.3.1.0' => [
                '6.3.1.0',
                [
                    'version' => '6.3.1.0',
                    'fullVersion' => '6.3.1.0',
                    'semVersion' => '3.1.0',
                    'prefix' => 6,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => '',
                    'buildMeta' => '',
                ],
            ],
            '7.3.1.0' => [
                '7.3.1.0',
                [
                    'version' => '7.3.1.0',
                    'fullVersion' => '7.3.1.0',
                    'semVersion' => '3.1.0',
                    'prefix' => 7,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => '',
                    'buildMeta' => '',
                ],
            ],
            '8.3.1.0' => [
                '8.3.1.0',
                [
                    'version' => '8.3.1.0',
                    'fullVersion' => '8.3.1.0',
                    'semVersion' => '3.1.0',
                    'prefix' => 8,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => '',
                    'buildMeta' => '',
                ],
            ],
            '8.3.1.0-dev' => [
                '8.3.1.0-dev',
                [
                    'version' => '8.3.1.0',
                    'fullVersion' => '8.3.1.0-dev',
                    'semVersion' => '3.1.0-dev',
                    'prefix' => 8,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => 'dev',
                    'buildMeta' => '',
                ],
            ],
            '8.3.1.0+test.build' => [
                '8.3.1.0+test.build',
                [
                    'version' => '8.3.1.0',
                    'fullVersion' => '8.3.1.0+test.build',
                    'semVersion' => '3.1.0+test.build',
                    'prefix' => 8,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => '',
                    'buildMeta' => 'test.build',
                ],
            ],
            '8.3.1.0-beta.1+build.156' => [
                '8.3.1.0-beta.1+build.156',
                [
                    'version' => '8.3.1.0',
                    'fullVersion' => '8.3.1.0-beta.1+build.156',
                    'semVersion' => '3.1.0-beta.1+build.156',
                    'prefix' => 8,
                    'major' => 3,
                    'minor' => 1,
                    'patch' => 0,
                    'preRelease' => 'beta.1',
                    'buildMeta' => 'build.156',
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareGreater()
    {
        return [
            ['6.3.2.0', false],
            ['1', true],
            ['1.2', true],
            ['1.2.3', true],
            ['2', true],
            ['2.0', true],
            ['1.3', true],
            ['1.2.4', true],
            ['6.3.3.5', false],
            ['1.1', true],
            ['1.2.2', true],
            ['6.2.3.3', true],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareGreaterEqual()
    {
        return [
            ['6.2.3.4', true],
            ['1', true],
            ['1.2', true],
            ['1.2.3', true],
            ['2', true],
            ['2.0', true],
            ['1.3', true],
            ['1.2.4', true],
            ['6.3.3.5', false],
            ['1.1', true],
            ['1.2.2', true],
            ['6.2.3.3', true],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareLess()
    {
        return [
            ['6.2.3.4', false],
            ['1', false],
            ['1.2', false],
            ['1.2.3', false],
            ['2', false],
            ['2.0', false],
            ['1.3', false],
            ['1.2.4', false],
            ['6.3.3.5', true],
            ['1.1', false],
            ['1.2.2', false],
            ['6.2.3.3', false],
        ];
    }

    /**
     * @return array[]
     */
    public function getAnotherCompareGreater()
    {
        return [
            ['1.2.0', true],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareLessEqual()
    {
        return [
            ['6.3.3.4', true],
            ['1', false],
            ['1.2', false],
            ['1.2.3', false],
            ['2', false],
            ['2.0', false],
            ['1.3', false],
            ['1.2.4', false],
            ['6.3.3.5', true],
            ['1.1', false],
            ['1.2.2', false],
            ['6.2.3.3', false],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareEqual()
    {
        return [
            ['6.3.1.0', true],
            ['1', false],
            ['1.2', false],
            ['1.2.3', false],
            ['2', false],
            ['2.0', false],
            ['1.3', false],
            ['1.2.4', false],
            ['6.3.3.5', false],
            ['1.1', false],
            ['1.2.2', false],
            ['6.3.3.3', false],
        ];
    }

    /**
     * @return array[]
     */
    public function getCompareNotEqual()
    {
        return [
            ['6.3.1.0', false],
            ['1', true],
            ['1.2', true],
            ['1.2.3', true],
            ['2', true],
            ['2.0', true],
            ['1.3', true],
            ['1.2.4', true],
            ['6.3.3.5', true],
            ['1.1', true],
            ['1.2.2', true],
            ['6.2.3.3', true],
        ];
    }

    /**
     * @return array[]
     */
    public function getInvalidVersions()
    {
        return [
            ['1.2.3.1.x'],
            ['2.x'],
            ['2   '],
            [' 1  '],
            ['11.'],
            ['.2'],
            ['1.2-beta_1'],
            ['1.2+dev@beta'],
            ['1.2#hashtag'],
        ];
    }

    /**
     * @param bool $result
     *
     * @return string
     */
    private function getVerb($result)
    {
        return $result ? 'is' : 'is NOT';
    }

    /**
     * @return array[]
     */
    public function getTwoVersionsToCompare()
    {
        return [
            // incremental build versions
            ['8.3.1.0+build.1', '8.3.1.0+build.2', '<'],
            // incremental nightly versions
            ['8.3.1.0-dev+nightly.20190526', '8.3.1.0-dev+nightly.20190527', '<'],
            // dev is less than alpha
            ['8.3.1.0-dev+nightly.20190526', '8.3.1.0-alpha.1+build.156', '<'],
            // alpha 1 is less than alpha 2
            ['8.3.1.0-alpha.1', '8.3.1.0-alpha.2', '<'],
            // alpha is less than beta
            ['8.3.1.0-alpha.1', '8.3.1.0-beta.1', '<'],
            // beta is less than RC
            ['8.3.1.0-beta.1', '8.3.1.0-RC.1', '<'],
            // RC is less than final
            ['8.3.1.0-RC.1', '8.3.1.0', '<'],
        ];
    }
}
