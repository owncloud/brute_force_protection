<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <info@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann info@jankaritech.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use TestHelpers\SetupHelper;

require_once 'bootstrap.php';

/**
 * Context for brute force protection specific steps
 */
class BruteForceProtectionContext implements Context {
	use Ip;
	
	/**
	 * @var FeatureContext
	 */
	private $featureContext;

	private $savedSettings = [];
	/**
	 * @Given the administrator has set the bruteforceprotection settings to:
	 *
	 * @param TableNode $table settings to be set
	 * setting-value table without header
	 * possible settings: threshold-time, fail-tolerance, ban-period
	 *
	 */
	public function setTheBruteforceprotectionSettings(TableNode $settings) {
		foreach ($settings->getRowsHash() as $setting => $value) {
			$this->setBruteforceprotectionSetting($setting, $value);
		}
	}
	
	/**
	 *
	 * @param string $setting
	 * @param string $value
	 * @throws \Exception
	 * @return void
	 */
	public function setBruteforceprotectionSetting($setting, $value) {
		$settingName = $this->mapSettingName($setting);
		$occResult = SetupHelper::runOcc(
			[
				'config:app:set',
				'brute_force_protection',
				$settingName,
				'--value',
				$value
			]
		);
		if ($occResult['code'] !== "0") {
			throw new \Exception(
				__METHOD__ .
				"\ncould not set '$settingName' for brute_force_protection app\n" .
				"error message: " . $occResult['code']
			);
		}
	}

	/**
	 *
	 * @param string $setting
	 * @return string
	 */
	public function getBruteforceprotectionSetting($setting) {
		$settingName = $this->mapSettingName($setting);
		return \trim(SetupHelper::runOcc(
			[
				'config:app:get',
				'brute_force_protection',
				$settingName,
			]
		)['stdOut']);
	}

	/**
	 * maps the settings threshold-time, fail-tolerance, ban-period to names needed for occ
	 *
	 * @param string $setting
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function mapSettingName($setting) {
		switch ($setting) {
			case "threshold-time":
				$settingName = "brute_force_protection_time_threshold";
				break;
			case "fail-tolerance":
				$settingName = "brute_force_protection_fail_tolerance";
				break;
			case "ban-period":
				$settingName = "brute_force_protection_ban_period";
				break;
			default:
				throw new \InvalidArgumentException(
					"bruteforce setting can only be threshold-time || fail-tolerance || ban-period"
				);
		}
		return $settingName;
	}

	/**
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function setUpScenario(BeforeScenarioScope $scope) {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		SetupHelper::init(
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getOcPath()
		);
		$this->savedSettings['threshold-time'] =
			$this->getBruteforceprotectionSetting('threshold-time');
		$this->savedSettings['fail-tolerance'] =
			$this->getBruteforceprotectionSetting('fail-tolerance');
		$this->savedSettings['ban-period'] =
			$this->getBruteforceprotectionSetting('ban-period');
	}

	/**
	 * @AfterScenario
	 *
	 * make sure all created users get their ban-logs reset
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function cleanupBannedUsers() {
		$this->setBruteforceprotectionSetting('ban-period', '0');
		foreach ($this->featureContext->getCreatedUsers() as $user => $userData) {
			$this->featureContext->initializeUser(
				$user, $this->featureContext->getUserPassword($user)
			);
		}
	}

	/**
	 * @AfterScenario
	 */
	public function setBackAppSettings() {
		foreach ($this->savedSettings as $setting => $value) {
			$this->setBruteforceprotectionSetting($setting, $value);
		}
	}
}
