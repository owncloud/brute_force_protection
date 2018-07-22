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

namespace OCA\BruteForceProtection\AppInfo;

use OCA\BruteForceProtection\BruteForceProtectionConfig;
use \OCP\AppFramework\App;
use \OCA\BruteForceProtection\Throttle;
use \OCA\BruteForceProtection\Hooks;
use \OCA\BruteForceProtection\Db\DbService;

class Application extends App {

	public function __construct(array $urlParams=array()){
		parent::__construct('brute_force_protection', $urlParams);

		$container = $this->getContainer();

		$container->registerService('DbService', function($c) {
			return new DbService(
				$c->query('ServerContainer')->getDb(),
				$c->query('OCP\AppFramework\Utility\ITimeFactory'),
				$c->query('BruteForceProtectionConfig')
			);
		});

		$container->registerService('Throttle', function($c) {
			return new Throttle(
				$c->query('DbService'),
				$c->query('BruteForceProtectionConfig'),
				$c->query('OCP\IL10N'),
				$c->query('OCP\AppFramework\Utility\ITimeFactory')
			);
		});

		$container->registerService('BruteForceProtectionConfig', function($c) {
			return new BruteForceProtectionConfig(
				$c->query('OCP\IConfig')
			);
		});

		$container->registerService('Hooks', function($c) {
			return new Hooks(
				$c->query('ServerContainer')->getUserManager(),
				$c->query('Throttle'),
				$c->query('Request')
			);
		});

	}
}
