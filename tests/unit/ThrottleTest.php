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

use OCA\BruteForceProtection\Db\FailedLinkAccessMapper;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCA\BruteForceProtection\Throttle;
use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\ILogger;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ThrottleTest extends TestCase {

	/** @var Throttle */
	private $throttle;

	/** @var MockObject | FailedLoginAttemptMapper */
	private $loginAttemptMapper;

	/** @var MockObject | FailedLinkAccessMapper */
	private $linkAccessMapper;

	/** @var MockObject | BruteForceProtectionConfig */
	private $configMock;

	/** @var MockObject | IL10N */
	private $lMock;
	
	/** @var MockObject | ILogger */
	private $loggerMock;

	/** @var MockObject | ITimeFactory */
	private $timeFactoryMock;

	public function setUp(): void {
		parent::setUp();

		$this->loginAttemptMapper = $this->createMock(FailedLoginAttemptMapper::class);
		$this->linkAccessMapper = $this->createMock(FailedLinkAccessMapper::class);
		$this->lMock = $this->createMock(IL10N::class);
		$this->loggerMock = $this->createMock(ILogger::class);
		$this->timeFactoryMock = $this->createMock(ITimeFactory::class);
		$this->configMock = $this->createMock(BruteForceProtectionConfig::class);

		$this->throttle = new Throttle(
			$this->loginAttemptMapper,
			$this->linkAccessMapper,
			$this->configMock,
			$this->lMock,
			$this->loggerMock,
			$this->timeFactoryMock
		);
	}

	/**
	 * @dataProvider bruteForceForLoginTestData
	 * @param int $lastAttempt
	 * @param int $attemptCount
	 * @param int $banPeriod
	 * @param int $failTolerance
	 * @param int $time
	 */
	public function testApplyBruteForcePolicy($lastAttempt, $attemptCount, $banPeriod, $failTolerance, $time) {
		$this->expectException(\OC\User\LoginException::class);

		$this->loginAttemptMapper->expects($this->once())
			->method('getLastFailedLoginAttemptTimeForUidIpCombination')
			->with('test', '192.168.1.1')
			->will($this->returnValue($lastAttempt));
		$this->loginAttemptMapper->expects($this->once())
			->method('getFailedLoginCountForUidIpCombination')
			->with('test', '192.168.1.1')
			->will($this->returnValue($attemptCount));
		$this->configMock->expects($this->once())
			->method('getBruteForceProtectionBanPeriod')
			->will($this->returnValue($banPeriod));
		$this->configMock->expects($this->once())
			->method('getBruteForceProtectionFailTolerance')
			->will($this->returnValue($failTolerance));
		$this->timeFactoryMock->expects($this->once())
			->method('getTime')
			->will($this->returnValue($time));
		$this->throttle->applyBruteForcePolicyForLogin('test', '192.168.1.1');
	}
	public function bruteForceForLoginTestData() {
		return [
			[5, 5, 10, 4, 14],
			[0, 3, 300, 2, 250]
		];
	}

	/**
	 * @dataProvider bruteForceForLoginTestData
	 * @param int $lastAttempt
	 * @param int $attemptCount
	 * @param int $banPeriod
	 * @param int $failTolerance
	 * @param int $time
	 */
	public function testApplyBruteForcePolicyForLinkShare($lastAttempt, $attemptCount, $banPeriod, $failTolerance, $time) {
		$this->expectException(\OCA\BruteForceProtection\Exceptions\LinkAuthException::class);

		$this->linkAccessMapper->expects($this->once())
			->method('getLastFailedAccessTimeForTokenIpCombination')
			->with('test', '192.168.1.1')
			->will($this->returnValue($lastAttempt));
		$this->linkAccessMapper->expects($this->once())
			->method('getFailedAccessCountForTokenIpCombination')
			->with('test', '192.168.1.1')
			->will($this->returnValue($attemptCount));
		$this->configMock->expects($this->once())
			->method('getBruteForceProtectionBanPeriod')
			->will($this->returnValue($banPeriod));
		$this->configMock->expects($this->once())
			->method('getBruteForceProtectionFailTolerance')
			->will($this->returnValue($failTolerance));
		$this->timeFactoryMock->expects($this->once())
			->method('getTime')
			->will($this->returnValue($time));
		$this->throttle->applyBruteForcePolicyForLinkShare('test', '192.168.1.1');
	}
	public function bruteForceLinkShareTestData() {
		return [
			[5, 5, 10, 4, 14],
			[0, 3, 300, 2, 250]
		];
	}
}
