<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
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
	 * @param IUserManager $userManager
	 * @param Throttle $throttle
	 * @param IRequest $request
	 */
	public function __construct($userManager, $throttle, $request) {
		$this->userManager = $userManager;
		$this->throttle = $throttle;
		$this->request = $request;
	}

	public function register() {
		$this->userManager->listen('\OC\User', 'preLogin', function($uid) {
			$this->preLoginCallback($uid);
		});

		$this->userManager->listen('\OC\User', 'failedLogin', function($uid) {
			$this->failedLoginCallback($uid);
		});
		
		$this->userManager->listen('\OC\User', 'postLogin', function($user) {
			/** @var $user \OC\User\User */
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
