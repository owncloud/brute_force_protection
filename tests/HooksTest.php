<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, Istanbul Technical University IT Department.
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


use OC\User\Manager;
use OCA\BruteForceProtection\Hooks;
use OCA\BruteForceProtection\Throttle;
use OCP\IRequest;
use Test\TestCase;

class HooksTest extends TestCase {

	/** @var  Hooks */
	private $hooks;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | Manager
	 */
	private $userManagerMock;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | Throttle
	 */
	private $throttleMock;
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject | IRequest
	 */
	private $requestMock;

	public function setUp() {
		parent::setUp();

		$this->userManagerMock = $this->getMockBuilder('\OC\User\Manager')
			->disableOriginalConstructor()
			->getMock();

		$this->throttleMock = $this->getMockBuilder('OCA\BruteForceProtection\Throttle')
			->disableOriginalConstructor()
			->getMock();
		$this->requestMock = $this->getMockBuilder('OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();

		$this->hooks = new Hooks(
			$this->userManagerMock,
			$this->throttleMock,
			$this->requestMock);
	}

	public function testRegister() {
		$this->userManagerMock->expects($this->exactly(3))
			->method('listen');
		$this->hooks->register();
	}

	public function testFailedLoginCallback() {
		$this->throttleMock->expects($this->once())
			->method('addFailedLoginAttempt');

		$this->hooks->failedLoginCallback("test");
		$this->assertTrue(true);
	}

	public function testPostLoginCallback() {
		$this->throttleMock->expects($this->once())
			->method('clearSuspiciousAttemptsForUidIpCombination');

		$this->hooks->postLoginCallback("test");
		$this->assertTrue(true);
	}

	public function testPreLoginCallback() {
		$this->throttleMock->expects($this->once())
			->method('applyBruteForcePolicy');

		$this->hooks->preLoginCallback('test');
		$this->assertTrue(true);
	}
}
