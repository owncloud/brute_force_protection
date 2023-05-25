<?php declare(strict_types=1);

/**
 * ownCloud
 *
 * @author Artur Neumann <artur@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann artur@jankaritech.com
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

namespace Page;

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * PageObject for the brute force protection Page
 *
 */
class BruteForceProtectionPage extends OwncloudPage {
	/**
	 *
	 * @var string $path
	 */
	protected $path = '/index.php/settings/admin?sectionid=security';
	private $thresholdTimeInputId = 'bfp-threshold-time';
	private $failToleranceInputId = 'bfp-fail-tolerance';
	private $banPeriodInputId = 'bfp-ban-period';
	private $saveButtonId = 'save-bfp-settings';

	/**
	 * fills the input field of the setting with the given value
	 *
	 * @param string $setting threshold-time|fail-tolerance|ban-period
	 * @param string|int $value
	 *
	 * @throws \InvalidArgumentException
	 * @throws ElementNotFoundException
	 *
	 * @return BruteForceProtectionPage
	 */
	public function fillSettingInput(
		string $setting,
		$value
	): BruteForceProtectionPage {
		switch ($setting) {
			case "threshold-time":
				$fieldToFillId = $this->thresholdTimeInputId;
				break;
			case "fail-tolerance":
				$fieldToFillId = $this->failToleranceInputId;
				break;
			case "ban-period":
				$fieldToFillId = $this->banPeriodInputId;
				break;
			default:
				throw new \InvalidArgumentException(
					"bruteforce setting can only be threshold-time || fail-tolerance || ban-period"
				);
		}
		$fieldToFill = $this->findById($fieldToFillId);
		if ($fieldToFill === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" id $fieldToFillId" .
				"could not find settings input field for '$setting'"
			);
		}
		$fieldToFill->setValue($value);
		return $this;
	}

	/**
	 * saves the settings
	 *
	 * @param Session $session
	 *
	 * @throws ElementNotFoundException
	 * @return BruteForceProtectionPage
	 */
	public function saveSettings(
		Session $session
	): BruteForceProtectionPage {
		$saveButton = $this->findById($this->saveButtonId);
		if ($saveButton === null) {
			throw new ElementNotFoundException(
				__METHOD__ .
				" id $this->saveButtonId" .
				"could not find the save-button"
			);
		}
		$saveButton->click();
		$this->waitForAjaxCallsToStartAndFinish($session);
		return $this;
	}

	/**
	 * sets one setting and saves the settings
	 *
	 * @param Session $session
	 * @param string $setting threshold-time|fail-tolerance|ban-period
	 * @param string|int $value
	 *
	 * @return BruteForceProtectionPage
	 */
	public function setSetting(
		Session $session,
		string $setting,
		$value
	): BruteForceProtectionPage {
		$this->fillSettingInput($setting, $value);
		$this->saveSettings($session);
		return $this;
	}

	/**
	 * sets multiple settings and saves them
	 *
	 * @param Session $session
	 * @param array $settings associated array ['setting-name' => 'value']
	 *
	 * @return BruteForceProtectionPage
	 */
	public function setSettings(
		Session $session,
		array $settings
	): BruteForceProtectionPage {
		foreach ($settings as $setting => $value) {
			$this->fillSettingInput($setting, $value);
		}
		$this->saveSettings($session);
		return $this;
	}
}
