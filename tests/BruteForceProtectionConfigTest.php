<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

namespace OCA\BruteForceProtection\Tests;

use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\IConfig;
use Test\TestCase;

class BruteForceProtectionConfigTest extends TestCase {
	/** @var  IConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;
	/** @var  BruteForceProtectionConfig */
	private $bfpConfig;

	public function setUp() {
		parent::setUp();
		$this->config = $this->createMock(IConfig::class);
		$this->bfpConfig = new BruteForceProtectionConfig($this->config);
	}

	/**
	 * @param array $mockedMethods
	 * @return BruteForceProtectionConfig | \PHPUnit_Framework_MockObject_MockObject
	 */
	private function getMockInstance($mockedMethods = []) {
		$passwordValidator = $this->getMockBuilder('OCA\BruteForceProtection\BruteForceProtectionConfig')
			->setConstructorArgs([$this->config])
			->setMethods($mockedMethods)->getMock();
		return $passwordValidator;
	}

	public function testGetBruteForceProtectionConfigs() {
		$instance = $this->getMockInstance(
			[
				'getBruteForceProtectionFailTolerance',
				'getBruteForceProtectionTimeThreshold',
				'getBruteForceProtectionBanPeriod',
			]
		);
		$instance->expects($this->once())->method('getBruteForceProtectionFailTolerance');
		$instance->expects($this->once())->method('getBruteForceProtectionTimeThreshold');
		$instance->expects($this->once())->method('getBruteForceProtectionBanPeriod');
		$instance->getBruteForceProtectionConfigs();
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $appConfigValue
	 * @param int $expected
	 */
	public function testGetBruteForceProtectionFailTolerance($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('brute_force_protection', 'brute_force_protection_fail_tolerance', '3')
			->willReturn($appConfigValue);
		$this->assertSame($expected,
			$this->bfpConfig->getBruteForceProtectionFailTolerance()
		);
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $appConfigValue
	 * @param int $expected
	 */
	public function testGetBruteForceProtectionTimeThreshold($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('brute_force_protection', 'brute_force_protection_time_threshold', '60')
			->willReturn($appConfigValue);
		$this->assertSame($expected,
			$this->bfpConfig->getBruteForceProtectionTimeThreshold()
		);
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $appConfigValue
	 * @param int $expected
	 */
	public function testGetBruteForceProtectionBanPeriod($appConfigValue, $expected) {
		$this->config->expects($this->once())->method('getAppValue')
			->with('brute_force_protection', 'brute_force_protection_ban_period', '300')
			->willReturn($appConfigValue);
		$this->assertSame($expected,
			$this->bfpConfig->getBruteForceProtectionBanPeriod()
		);
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $expected
	 * @param int $setValue
	 */
	public function testSetBruteForceProtectionFailTolerance($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('brute_force_protection', 'brute_force_protection_fail_tolerance', $expected);
		$this->bfpConfig->setBruteForceProtectionFailTolerance($setValue);
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $expected
	 * @param int $setValue
	 */
	public function testSetBruteForceProtectionTimeThreshold($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('brute_force_protection', 'brute_force_protection_time_threshold', $expected);
		$this->bfpConfig->setBruteForceProtectionTimeThreshold($setValue);
	}

	/**
	 * @dataProvider numericConfTestData
	 * @param string $expected
	 * @param int $setValue
	 */
	public function testSetBruteForceProtectionBanPeriod($expected, $setValue) {
		$this->config->expects($this->once())->method('setAppValue')
			->with('brute_force_protection', 'brute_force_protection_ban_period', $expected);
		$this->bfpConfig->setBruteForceProtectionBanPeriod($setValue);
	}

	public function configTestData() {
		return [
			['1', true],
			['0', false],
		];
	}

	public function numericConfTestData() {
		return [
			['42', 42],
			['300', 300],
			['1000', 1000]
		];
	}
}
