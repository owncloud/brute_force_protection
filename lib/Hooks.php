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

namespace OCA\BruteForceProtection;

use OC\User\LoginException;
use OCA\BruteForceProtection\Db\FailedLinkAccess;
use OCA\BruteForceProtection\Db\FailedLinkAccessMapper;
use OCA\BruteForceProtection\Db\FailedLoginAttempt;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCA\BruteForceProtection\Exceptions\LinkAuthException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Hooks
 * @package OCA\BruteForceProtection\Lib
 */
class Hooks {

	/** @var Throttle */
	private $throttle;

	/** @var IRequest */
	private $request;

	/** @var FailedLoginAttemptMapper */
	private $loginAttemptMapper;

	/** @var FailedLinkAccessMapper */
	private $linkAccessMapper;

	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	/** @var ITimeFactory */
	private $timeFactory;

	/**
	 * @param Throttle $throttle
	 * @param IRequest $request
	 * @param FailedLoginAttemptMapper $loginAttemptMapper
	 * @param FailedLinkAccessMapper $linkAccessMapper
	 * @param EventDispatcherInterface $eventDispatcher
	 * @param ITimeFactory $timeFactory
	 */
	public function __construct(
		Throttle $throttle,
		IRequest $request,
		FailedLoginAttemptMapper $loginAttemptMapper,
		FailedLinkAccessMapper $linkAccessMapper,
		EventDispatcherInterface $eventDispatcher,
		ITimeFactory $timeFactory
	) {
		$this->throttle = $throttle;
		$this->request = $request;
		$this->loginAttemptMapper = $loginAttemptMapper;
		$this->linkAccessMapper = $linkAccessMapper;
		$this->eventDispatcher = $eventDispatcher;
		$this->timeFactory = $timeFactory;
	}

	public function register() {
		/* Login events */
		$this->eventDispatcher->addListener('user.loginfailed', [$this, 'failedLoginCallback']);
		$this->eventDispatcher->addListener('user.afterlogin', [$this, 'postLoginCallback']);
		$this->eventDispatcher->addListener('user.beforelogin', [$this, 'preLoginCallback']);

		/* Public link share events */
		$this->eventDispatcher->addListener('share.failedpasswordcheck', [$this, 'failedLinkShareAuthCallback']);
		$this->eventDispatcher->addListener('share.afterpasswordcheck', [$this, 'postLinkShareAuthCallback']);
		$this->eventDispatcher->addListener('share.beforepasswordcheck', [$this, 'preLinkShareAuthCallback']);
	}

	/**
	 * @param GenericEvent $event
	 */
	public function failedLoginCallback($event) {
		$uid = $event->getArgument('user');
		// apply policy to throw the login exception if needed.
		// The failed login attempt won't be stored if the exception is thrown (same behavior with OC 10.11 and earlier)
		$this->throttle->applyBruteForcePolicyForLogin($uid, $this->request->getRemoteAddress());

		$attempt = new FailedLoginAttempt();
		$attempt->setUid($uid);
		$attempt->setIp($this->request->getRemoteAddress());
		$attempt->setAttemptedAt($this->timeFactory->getTime());
		$this->loginAttemptMapper->insert($attempt);
	}

	/**
	 * @param GenericEvent $event
	 */
	public function postLoginCallback($event) {
		/** @var \OCP\IUser $user */
		$user = $event->getArgument('user');
		$this->loginAttemptMapper->deleteFailedLoginAttemptsForUidIpCombination($user->getUID(), $this->request->getRemoteAddress());
	}

	/**
	 * @param GenericEvent $event
	 * @throws LoginException
	 */
	public function preLoginCallback($event) {
		$uid = $event->getArgument('login');
		$this->throttle->applyBruteForcePolicyForLogin($uid, $this->request->getRemoteAddress());
	}

	/**
	 * @param GenericEvent $event
	 */
	public function failedLinkShareAuthCallback($event) {
		/** @var \OCP\Share\IShare $share */
		$share = $event->getArgument('shareObject');
		$access = new FailedLinkAccess();
		$access->setLinkToken($share->getToken());
		$access->setIp($this->request->getRemoteAddress());
		$access->setAttemptedAt($this->timeFactory->getTime());
		$this->linkAccessMapper->insert($access);
	}

	/**
	 * @param GenericEvent $event
	 */
	public function postLinkShareAuthCallback($event) {
		/** @var \OCP\Share\IShare $share */
		$share = $event->getArgument('shareObject');
		$this->linkAccessMapper->deleteFailedAccessForTokenIpCombination($share->getToken(), $this->request->getRemoteAddress());
	}

	/**
	 * @param GenericEvent $event
	 * @throws LinkAuthException
	 */
	public function preLinkShareAuthCallback($event) {
		/** @var \OCP\Share\IShare $share */
		$share = $event->getArgument('shareObject');
		$this->throttle->applyBruteForcePolicyForLinkShare($share->getToken(), $this->request->getRemoteAddress());
	}
}
