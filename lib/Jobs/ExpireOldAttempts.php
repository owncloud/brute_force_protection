<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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
namespace OCA\BruteForceProtection\Jobs;
use OC\BackgroundJob\TimedJob;
use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;

class ExpireOldAttempts extends TimedJob {
	/** @var FailedLoginAttemptMapper $mapper */
	private $mapper;
	/** @var BruteForceProtectionConfig $config */
	private $config;
	public function __construct(
		FailedLoginAttemptMapper $mapper,
		BruteForceProtectionConfig $config
	) {
		// Run once a day
		$this->setInterval(24 * 60 * 60);
		$this->mapper = $mapper;
		$this->config = $config;
	}

	public function run($argument) {
		$threshold = $this->config->getBruteForceProtectionTimeThreshold() + $this->config->getBruteForceProtectionBanPeriod();
		$this->mapper->deleteOldFailedLoginAttempts($threshold);
	}
}
