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

use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCA\BruteForceProtection\Throttle;
use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use Test\TestCase;

class ThrottleTest extends TestCase {

	/** @var Throttle */
	private $throttle;
	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject | FailedLoginAttemptMapper
	 */
	private $attemptMapper;
	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject | BruteForceProtectionConfig
	 */
	private $configMock;
	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject | IL10N
	 */
	private $lMock;
	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject | ITimeFactory
	 */
	private $timeFactoryMock;

	public function setUp(): void {
		parent::setUp();

		$this->attemptMapper = $this->getMockBuilder(FailedLoginAttemptMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->lMock = $this->getMockBuilder(IL10N::class)
			->disableOriginalConstructor()
			->getMock();
		$this->timeFactoryMock = $this->getMockBuilder(ITimeFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$this->configMock = $this->getMockBuilder(BruteForceProtectionConfig::class)
			->disableOriginalConstructor()
			->getMock();

		$this->throttle = new Throttle(
			$this->attemptMapper,
			$this->configMock,
			$this->lMock,
			$this->timeFactoryMock
		);
	}

	public function testAddFailedLoginAttempt() {
		$this->attemptMapper->expects($this->once())->method('insert');

		$this->throttle->addFailedLoginAttempt('test', '192.168.1.1');
	}

	/**
	 * @dataProvider bruteForceTestData
	 * @param int $lastAttempt
	 * @param int $attemptCount
	 * @param int $banPeriod
	 * @param int $failTolerance
	 * @param int $time
	 */
	public function testApplyBruteForcePolicy($lastAttempt, $attemptCount, $banPeriod, $failTolerance, $time) {
		$this->expectException(\OC\User\LoginException::class);

		$this->attemptMapper->expects($this->once())
			->method('getLastFailedLoginAttemptTimeForUidIpCombination')
			->with('test', '192.168.1.1')
			->will($this->returnValue($lastAttempt));
		$this->attemptMapper->expects($this->once())
			->method('getSuspiciousActivityCountForUidIpCombination')
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
		$this->throttle->applyBruteForcePolicy('test', '192.168.1.1');
	}
	public function bruteForceTestData() {
		return [
			[5, 5, 10, 4, 14],
			[0, 3, 300, 2, 250]
		];
	}
}
