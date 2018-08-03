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
use OCA\BruteForceProtection\Db\FailedLoginAttempt;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;

/**
 * Class Throttle
 * @package OCA\BruteForceProtection\Lib
 */
class Throttle {

	/**
	 * @var FailedLoginAttemptMapper $attemptMapper
	 */
	protected $attemptMapper;

	/**
	 * @var BruteForceProtectionConfig $config
	 */
	protected $config;

	/**
	 * @var IL10N $l
	 */
	protected $l;

	/**
	 * @var ITimeFactory $timeFactory
	 */
	protected $timeFactory;

	/**
	 * @param FailedLoginAttemptMapper $attemptMapper
	 * @param BruteForceProtectionConfig $config
	 * @param IL10N $l
	 * @param ITimeFactory $timeFactory
	 */
	public function __construct(
		FailedLoginAttemptMapper $attemptMapper,
		BruteForceProtectionConfig $config,
		IL10N $l,
		ITimeFactory $timeFactory
	) {
		$this->attemptMapper = $attemptMapper;
		$this->config = $config;
		$this->l = $l;
		$this->timeFactory = $timeFactory;
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @return void
	 */
	public function addFailedLoginAttempt($uid, $ip) {
		$attempt = new FailedLoginAttempt();
		$attempt->setUid($uid);
		$attempt->setIp($ip);
		$attempt->setAttemptedAt($this->timeFactory->getTime());
		$this->attemptMapper->insert($attempt);
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @throws LoginException
	 */
	public function applyBruteForcePolicy($uid ,$ip) {
		$banPeriod = $this->config->getBruteForceProtectionBanPeriod();
		$banUntil = $this->attemptMapper->getLastFailedLoginAttemptTimeForIp($ip)+$banPeriod;
		if($this->attemptMapper->getSuspiciousActivityCountForUidIpCombination($uid, $ip) >=
			$this->config->getBruteForceProtectionFailTolerance() &&
			$banUntil > $this->timeFactory->getTime()) {
			throw new LoginException($this->l->t("Too many failed login attempts. Try again in %s minutes.",
				ceil($banPeriod/60))
			);
		}
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @return void
	 */
	public function clearSuspiciousAttemptsForUidIpCombination($uid, $ip) {
		$this->attemptMapper->deleteSuspiciousAttemptsForUidIpCombination($uid, $ip);
	}
}
