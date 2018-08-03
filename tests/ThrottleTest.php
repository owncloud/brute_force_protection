<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */


namespace OCA\BruteForceProtection\Tests;


use OC\User\LoginException;
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
	 * @var \PHPUnit_Framework_MockObject_MockObject | FailedLoginAttemptMapper
	 */
	private $attemptMapper;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | BruteForceProtectionConfig
	 */
	private $configMock;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | IL10N
	 */
	private $lMock;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | ITimeFactory
	 */
	private $timeFactoryMock;

	public function setUp() {
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
		$this->timeFactoryMock
			->method('getTime')
			->willReturn(\time());
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
	 * @throws LoginException
	 */
	public function testApplyBruteForcePolicy($lastAttempt, $attemptCount, $banPeriod, $failTolerance, $time) {
		$this->attemptMapper->expects($this->once())
			->method('getLastFailedLoginAttemptTimeForIp')
			->with('192.168.1.1')
			->will($this->returnValue($lastAttempt));
		$this->attemptMapper->expects($this->once())
			->method('getSuspiciousActivityCountForUidIpCombination')
			->with('test','192.168.1.1')
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
		$this->throttle->applyBruteForcePolicy('test','192.168.1.1');
	}
	public function bruteForceTestData() {
		return [
			[5, 5, 10, 4, 14],
			[0, 3, 300, 2, 250]
		];
	}
}
