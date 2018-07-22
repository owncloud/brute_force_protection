<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH.
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

/** @var array $_ */
/** @var \OCP\IL10N $l */
script('brute_force_protection', 'settings-admin');
?>
<div id="brute-force-protection" class="section">
	<h2 class="inlineblock"><?php p($l->t('Brute Force Protection')); ?></h2>
	<span id="save-bfp-settings-message" class="msg"></span>
	<div>
		<label for="bfp-threshold-time"><?php p($l->t('Count failed login attempts over how many seconds?')) ?></label><br>
		<input type="number" id="bfp-threshold-time"  value="<?php p($_['bruteForceProtectionTimeThreshold']) ?>"><br>
		<label for="bfp-fail-tolerance"><?php p($l->t('Ban after how many failed login attempts?')) ?></label><br>
		<input type="number" id="bfp-fail-tolerance"  value="<?php p($_['bruteForceProtectionFailTolerance']) ?>"><br>
		<label for="bfp-ban-period"><?php p($l->t('Ban for how many seconds?')) ?></label><br>
		<input type="number" id="bfp-ban-period" value="<?php p($_['bruteForceProtectionBanPeriod']) ?>"><br>
		<button id="save-bfp-settings" class="save"><?php p($l->t('Save settings'));?></button>
	</div>
</div>
