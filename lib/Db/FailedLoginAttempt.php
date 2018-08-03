<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OCA\BruteForceProtection\Db;

use OCP\AppFramework\Db\Entity;
/**
 * @method int getId()
 * @method void setId(\int $id)
 * @method string getIp()
 * @method void setIp(\string $ip)
 * @method string getUid()
 * @method void setUid(\string $uid)
 * @method int getAttemptedAt()
 * @method void setAttemptedAt(\int $attemptedAt)
 */
class FailedLoginAttempt extends Entity {

	/** @var string $ip */
	protected $ip;

	/** @var string $uid */
	protected $uid;

	/** @var int $attemptedAt */
	protected $attemptedAt;
}