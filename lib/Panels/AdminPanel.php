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

namespace OCA\BruteForceProtection\Panels;

use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\Settings\ISettings;
use OCP\Template;

class AdminPanel implements ISettings {

	/** @var BruteForceProtectionConfig */
	private $config;

	public function __construct(BruteForceProtectionConfig $config) {
		$this->config = $config;
	}

	public function getPanel() {
		$params = $this->config->getBruteForceProtectionConfigs();
		$tmpl = new Template('brute_force_protection', 'settings-admin');
		foreach ($params as $key => $value) {
			$tmpl->assign($key, $value);
		}
		return $tmpl;
	}

	public function getSectionID() {
		return 'security';
	}

	public function getPriority() {
		return 100;
	}
}
