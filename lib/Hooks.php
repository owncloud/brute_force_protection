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
use OCA\BruteForceProtection\Exceptions\LinkAuthException;
use OCP\IRequest;
use OCP\IUser;
use OCP\Share\IShare;
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

	/** @var EventDispatcherInterface */
	private $eventDispatcher;

	/**
	 * @param Throttle $throttle
	 * @param IRequest $request
	 * @param EventDispatcherInterface $eventDispatcher
	 */
	public function __construct(
		Throttle $throttle,
		IRequest $request,
		EventDispatcherInterface $eventDispatcher
	) {
		$this->throttle = $throttle;
		$this->request = $request;
		$this->eventDispatcher = $eventDispatcher;
	}

	public function register() {
		/* Login events */
		$this->eventDispatcher->addListener('user.loginfailed', [$this, 'failedLoginCallback']);
		$this->eventDispatcher->addListener('user.afterlogin', [$this, 'postLoginCallback']);
		$this->eventDispatcher->addListener('user.beforelogin', [$this, 'preLoginCallback']);

		/* Public link share events */
		$this->eventDispatcher->addListener('share.linkaccess', [$this, 'linkAccessCallback']);
		$this->eventDispatcher->addListener('share.afterlinkauth', [$this, 'postLinkAuthCallback']);
		$this->eventDispatcher->addListener('share.beforelinkauth', [$this, 'preLinkAuthCallback']);
	}

	/**
	 * @param GenericEvent $event
	 */
	public function failedLoginCallback($event) {
		$uid = $event->getArgument('user');
		$this->throttle->addFailedLoginAttempt($uid, $this->request->getRemoteAddress());
	}

	/**
	 * @param GenericEvent $event
	 */
	public function postLoginCallback($event) {
		/** @var IUser $user */
		$user = $event->getArgument('user');
		$this->throttle->clearFailedLoginAttemptsForUidIpCombination($user->getUID(), $this->request->getRemoteAddress());
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
	public function linkAccessCallback($event) {
		/** @var IShare $share */
		$share = $event->getArgument('shareObject');
		$errorCode = $event->getArgument('errorCode');
		if ($errorCode === 403) {
			$this->throttle->addFailedLinkAccess($share->getToken(), $this->request->getRemoteAddress());
		}
	}

	/**
	 * @param GenericEvent $event
	 */
	public function postLinkAuthCallback($event) {
		/** @var IShare $share */
		$share = $event->getArgument('shareObject');
		$this->throttle->clearFailedLinkAccesses($share->getToken(), $this->request->getRemoteAddress());
	}

	/**
	 * @param GenericEvent $event
	 * @throws LinkAuthException
	 */
	public function preLinkAuthCallback($event) {
		/** @var IShare $share */
		$share = $event->getArgument('shareObject');
		$this->throttle->applyBruteForcePolicyForLinkShare($share->getToken(), $this->request->getRemoteAddress());
	}
}
