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
use OCA\BruteForceProtection\Db\FailedLinkAccessMapper;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCA\BruteForceProtection\Exceptions\LinkAuthException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\ILogger;

/**
 * Class Throttle
 * @package OCA\BruteForceProtection\Lib
 */
class Throttle {

	/** @var FailedLoginAttemptMapper $loginAttemptMapper */
	protected $loginAttemptMapper;

	/** @var FailedLinkAccessMapper $linkAccessMapper */
	protected $linkAccessMapper;

	/** @var BruteForceProtectionConfig $config */
	protected $config;

	/** @var IL10N $l */
	protected $l;

	/** @var ILogger $logger */
	private $logger;
	
	/** @var ITimeFactory $timeFactory */
	protected $timeFactory;

	/**
	 * @param FailedLoginAttemptMapper $loginAttemptMapper
	 * @param FailedLinkAccessMapper $linkAccessMapper
	 * @param BruteForceProtectionConfig $config
	 * @param IL10N $l
	 * @param ITimeFactory $timeFactory
	 */
	public function __construct(
		FailedLoginAttemptMapper $loginAttemptMapper,
		FailedLinkAccessMapper $linkAccessMapper,
		BruteForceProtectionConfig $config,
		IL10N $l,
		ILogger $logger,
		ITimeFactory $timeFactory
	) {
		$this->loginAttemptMapper = $loginAttemptMapper;
		$this->linkAccessMapper = $linkAccessMapper;
		$this->config = $config;
		$this->l = $l;
		$this->logger = $logger;
		$this->timeFactory = $timeFactory;
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @throws LoginException
	 * return void
	 */
	public function applyBruteForcePolicyForLogin($uid, $ip) {
		$banPeriod = $this->config->getBruteForceProtectionBanPeriod();
		$lastAttempt = $this->loginAttemptMapper->getLastFailedLoginAttemptTimeForUidIpCombination($uid, $ip);
		if ($lastAttempt === null || ($lastAttempt + $banPeriod) <= $this->timeFactory->getTime()) {
			return;
		}
		$thresholdTime = $lastAttempt - $this->config->getBruteForceProtectionTimeThreshold();
		if ($this->loginAttemptMapper->getFailedLoginCountForUidIpCombination($uid, $ip, $thresholdTime) >=
			$this->config->getBruteForceProtectionFailTolerance()) {
			$this->logger->warning('User \''. $uid .'\' is blocked for '. $this->parseBanPeriodForHumans($banPeriod) .'. Too many failed login attempts', ['app' => 'brute_force_protection']);
			throw new LoginException(
				$this->l->t(
					"Too many failed login attempts. Try again in %s.",
					[$this->parseBanPeriodForHumans($banPeriod)]
				)
			);
		}
	}

	/**
	 * @param string $token
	 * @param string $ip
	 * @throws LinkAuthException
	 * return void
	 */
	public function applyBruteForcePolicyForLinkShare($token, $ip) {
		$banPeriod = $this->config->getBruteForceProtectionBanPeriod();
		$lastAttempt = $this->linkAccessMapper->getLastFailedAccessTimeForTokenIpCombination($token, $ip) ;
		if ($lastAttempt === null || ($lastAttempt + $banPeriod) <= $this->timeFactory->getTime()) {
			return;
		}
		$thresholdTime = $lastAttempt - $this->config->getBruteForceProtectionTimeThreshold();
		if ($this->linkAccessMapper->getFailedAccessCountForTokenIpCombination($token, $ip, $thresholdTime) >=
			$this->config->getBruteForceProtectionFailTolerance()) {
			throw new LinkAuthException(
				$this->l->t(
					"Too many failed attempts. Try again in %s.",
					[$this->parseBanPeriodForHumans($banPeriod)]
				)
			);
		}
	}

	/**
	 * @param int $seconds
	 * @return string $banPeriodForHumans
	 */
	private function parseBanPeriodForHumans($seconds) {
		return ($seconds / 60 < 60)
			? $this->l->n(' %n minute', ' %n minutes', (int)\ceil($seconds/60))
			: $this->l->n(' %n hour', ' %n hours', (int)\ceil(($seconds/60)/60));
	}
}
