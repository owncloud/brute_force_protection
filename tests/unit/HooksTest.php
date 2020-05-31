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
use OCA\BruteForceProtection\Hooks;
use OCA\BruteForceProtection\Throttle;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use OCP\IUser;
use OCP\Share;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Test\TestCase;

class HooksTest extends TestCase {

	/** @var  Hooks */
	private $hooks;

	/** @var MockObject | Throttle */
	private $throttleMock;

	/** @var MockObject | IRequest */
	private $requestMock;

	/** @var MockObject | FailedLoginAttemptMapper */
	private $loginAttemptMapperMock;

	/** @var MockObject | FailedLinkAccessMapper */
	private $linkAccessMapperMock;

	/** @var MockObject | EventDispatcherInterface */
	private $eventDispatcherMock;

	/** @var MockObject | ITimeFactory */
	private $timeFactoryMock;

	public function setUp(): void {
		parent::setUp();
		$this->throttleMock = $this->createMock(Throttle::class);
		$this->requestMock = $this->createMock(IRequest::class);
		$this->loginAttemptMapperMock = $this->createMock(FailedLoginAttemptMapper::class);
		$this->linkAccessMapperMock = $this->createMock(FailedLinkAccessMapper::class);
		$this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
		$this->timeFactoryMock = $this->createMock(ITimeFactory::class);

		$this->hooks = new Hooks(
			$this->throttleMock,
			$this->requestMock,
			$this->loginAttemptMapperMock,
			$this->linkAccessMapperMock,
			$this->eventDispatcherMock,
			$this->timeFactoryMock
		);
	}

	public function testRegister() {
		$this->eventDispatcherMock->expects($this->exactly(6))
			->method('addListener');
		$this->hooks->register();
	}

	public function testFailedLoginCallback() {
		$event = new GenericEvent(null, ['user' => 'test']);
		$this->loginAttemptMapperMock->expects($this->once())
			->method('insert');
		$this->hooks->failedLoginCallback($event);
	}

	public function testPostLoginCallback() {
		$mockUser = $this->createMock(IUser::class);
		$mockUser->method('getUID')->willReturn('test');
		$event = new GenericEvent(null, ['user' => $mockUser]);
		$this->loginAttemptMapperMock->expects($this->once())
			->method('deleteFailedLoginAttemptsForUidIpCombination');
		$this->hooks->postLoginCallback($event);
	}

	public function testPreLoginCallback() {
		$event = new GenericEvent(null, ['login' => 'test']);
		$this->throttleMock->expects($this->once())
			->method('applyBruteForcePolicyForLogin');
		$this->hooks->preLoginCallback($event);
	}

	public function testFailedLinkShareAuthCallback() {
		$share = $this->createMock('OCP\Share\IShare');
		$event = new GenericEvent(null, ['shareObject' => $share]);
		$this->linkAccessMapperMock->expects($this->once())
			->method('insert');
		$this->hooks->failedLinkShareAuthCallback($event);
	}

	public function testPostLinkShareAuthCallback() {
		$share = $this->createMock('OCP\Share\IShare');
		$event = new GenericEvent(null, ['shareObject' => $share]);
		$this->linkAccessMapperMock->expects($this->once())
			->method('deleteFailedAccessForTokenIpCombination');
		$this->hooks->postLinkShareAuthCallback($event);
	}

	public function testPreLinkShareAuthCallback() {
		$share = $this->createMock('OCP\Share\IShare');
		$event = new GenericEvent(null, ['shareObject' => $share]);
		$this->throttleMock->expects($this->once())
			->method('applyBruteForcePolicyForLinkShare');
		$this->hooks->preLinkShareAuthCallback($event);
	}
}
