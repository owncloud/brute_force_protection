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
use OCP\IUserManager;
use OCP\IRequest;

/**
 * Class Hooks
 * @package OCA\BruteForceProtection\Lib
 */
class Hooks {

	/** @var \OC\User\Manager */
	private $userManager;

	/** @var Throttle*/
	private $throttle;

	/** @var IRequest*/
	private $request;

	/**
	 * @param \OC\User\Manager $userManager
	 * @param Throttle $throttle
	 * @param IRequest $request
	 */
	public function __construct(
		IUserManager $userManager,
		Throttle $throttle,
		IRequest $request
	) {
		$this->userManager = $userManager;
		$this->throttle = $throttle;
		$this->request = $request;
	}

	public function register() {
		$this->userManager->listen('\OC\User', 'preLogin', function ($uid) {
			$this->preLoginCallback($uid);
		});

		$this->userManager->listen('\OC\User', 'failedLogin', function ($uid) {
			$this->failedLoginCallback($uid);
		});
		
		$this->userManager->listen('\OC\User', 'postLogin', function ($user) {
			/** @var $user \OCP\IUser */
			$this->postLoginCallback($user->getUID());
		});
	}

	/**
	 * @param string $uid
	 */
	public function failedLoginCallback($uid) {
		$this->throttle->addFailedLoginAttempt($uid, $this->request->getRemoteAddress());
	}

	/**
	 * @param string $uid
	 */
	public function postLoginCallback($uid) {
		$this->throttle->clearSuspiciousAttemptsForUidIpCombination($uid, $this->request->getRemoteAddress());
	}

	/**
	 * @param string $uid
	 * @throws LoginException
	 */
	public function preLoginCallback($uid) {
		$this->throttle->applyBruteForcePolicy($uid, $this->request->getRemoteAddress());
	}
}
