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

use OCP\IConfig;
/**
 * Class Config
 *
 * read/write config of the brute-force protection policies
 *
 * @package OCA\BruteForceProtection
 */
class BruteForceProtectionConfig {

	/** @var IConfig */
	private $config;

	/**
	 * Config constructor.
	 *
	 * @param IConfig $config
	 */
	public function __construct(IConfig $config) {
		$this->config = $config;
	}

	/**
	 * get brute force protection configurations
	 *
	 * @return array
	 */
	public function getBruteForceProtectionConfigs() {
		return [
			'bruteForceProtectionFailTolerance' => $this->getBruteForceProtectionFailTolerance(),
			'bruteForceProtectionTimeThreshold' => $this->getBruteForceProtectionTimeThreshold(),
			'bruteForceProtectionBanPeriod' => $this->getBruteForceProtectionBanPeriod(),
		];
	}

	/**
	 * ban after how many failed attempts
	 *
	 * @return int
	 */
	public function getBruteForceProtectionFailTolerance() {
		$tolerance = $this->config->getAppValue(
			'brute_force_protection',
			'brute_force_protection_fail_tolerance',
			'3'
		);
		return intval($tolerance);
	}

	/**
	 * Count failed login attempts over how many seconds
	 *
	 * @return int
	 */
	public function getBruteForceProtectionTimeThreshold() {
		$timeThreshold = $this->config->getAppValue(
			'brute_force_protection',
			'brute_force_protection_time_threshold',
			'600'
		);
		return intval($timeThreshold);
	}

	/**
	 * How many seconds to ban an attacker
	 *
	 * @return int
	 */
	public function getBruteForceProtectionBanPeriod() {
		$banPeriod = $this->config->getAppValue(
			'brute_force_protection',
			'brute_force_protection_ban_period',
			'300'
		);
		return intval($banPeriod);
	}

	/**
	 * @param int $attempts
	 */
	public function setBruteForceProtectionFailTolerance($attempts) {
		$this->config->setAppValue('brute_force_protection', 'brute_force_protection_fail_tolerance', $attempts);
	}

	/**
	 * @param int $seconds
	 */
	public function setBruteForceProtectionTimeThreshold($seconds) {
		$this->config->setAppValue('brute_force_protection', 'brute_force_protection_time_threshold', $seconds);
	}

	/**
	 * @param int $seconds
	 */
	public function setBruteForceProtectionBanPeriod($seconds) {
		$this->config->setAppValue('brute_force_protection', 'brute_force_protection_ban_period', $seconds);
	}
}
