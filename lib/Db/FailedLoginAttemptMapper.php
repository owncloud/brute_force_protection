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

namespace OCA\BruteForceProtection\Db;

use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IDBConnection;

/**
 * Class FailedLoginAttemptMapper
 * @package OCA\BruteForceProtection\Db
 */
class FailedLoginAttemptMapper extends Mapper {
	/**
	 * @var ITimeFactory $timeFactory
	 */
	protected $timeFactory;

	/**
	 * @var string $tableName
	 */
	protected $tableName = 'bfp_failed_logins';

	/**
	 * FailedLoginAttemptMapper constructor.
	 *
	 * @param IDBConnection $db
	 * @param ITimeFactory $timeFactory
	 */
	public function __construct(
		IDBConnection $db,
		ITimeFactory $timeFactory
	) {
		parent::__construct($db, $this->tableName);
		$this->timeFactory = $timeFactory;
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @param int $thresholdTime the timestamp where attempts will start counting
	 * @return int
	 */
	public function getFailedLoginCountForUidIpCombination($uid, $ip, $thresholdTime) {
		$builder = $this->db->getQueryBuilder();
		/* @phan-suppress-next-line PhanDeprecatedFunction */
		$attempts = $builder->selectAlias($builder->createFunction('COUNT(*)'), 'count')
			->from($this->tableName)
			->where($builder->expr()->gt('attempted_at', $builder->createNamedParameter($thresholdTime)))
			->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter($uid)))
			->andWhere($builder->expr()->eq('ip', $builder->createNamedParameter($ip)))
			->execute()
			->fetch();
		return \intval($attempts['count']);
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 * @return int|null unix timestamp of the last attempt or null if no prior attempt
	 */
	public function getLastFailedLoginAttemptTimeForUidIpCombination($uid, $ip) {
		$builder = $this->db->getQueryBuilder();
		/* @phan-suppress-next-line PhanDeprecatedFunction */
		$lastAttempt = $builder->select('attempted_at')
			->from($this->tableName)
			->where($builder->expr()->eq('uid', $builder->createNamedParameter($uid)))
			->andWhere($builder->expr()->eq('ip', $builder->createNamedParameter($ip)))
			->orderBy('attempted_at', 'DESC')
			->setMaxResults(1)
			->execute()
			->fetch();
		return ($lastAttempt === false) ? null : \intval($lastAttempt['attempted_at']);
	}

	/**
	 * @param string $uid
	 * @param string $ip
	 */
	public function deleteFailedLoginAttemptsForUidIpCombination($uid, $ip) {
		$builder = $this->db->getQueryBuilder();
		$builder->delete($this->tableName)
			->where($builder->expr()->eq('uid', $builder->createNamedParameter($uid)))
			->andWhere($builder->expr()->eq('ip', $builder->createNamedParameter($ip)))
			->execute();
	}

	/**
	 * It removes entries that were created before the specified threshold seconds.
	 *
	 * @param int $threshold the amount of threshold seconds
	 */
	public function deleteOldFailedLoginAttempts($threshold) {
		$builder = $this->db->getQueryBuilder();
		$thresholdTime = $this->timeFactory->getTime() - $threshold;
		$builder->delete($this->tableName)
			->where($builder->expr()->lt('attempted_at', $builder->createNamedParameter($thresholdTime)))
			->execute();
	}
}
