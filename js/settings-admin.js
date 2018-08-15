/**
 * @copyright Copyright (c) 2018, Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
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

$(document).ready(function(){
	$('#brute-force-protection').on('click','#save-bfp-settings',
		function() {
			OC.msg.startSaving('#save-bfp-settings-message');
			let thresholdTime = $('#bfp-threshold-time').val();
			let failTolerance = $('#bfp-fail-tolerance').val();
			let banPeriod = $('#bfp-ban-period').val();
			if($.isNumeric(thresholdTime) && thresholdTime>0 &&
				$.isNumeric(failTolerance) && failTolerance>0 &&
				$.isNumeric(banPeriod) && banPeriod>0) {
				OC.AppConfig.setValue('brute_force_protection', 'brute_force_protection_time_threshold', thresholdTime);
				OC.AppConfig.setValue('brute_force_protection', 'brute_force_protection_fail_tolerance', failTolerance);
				OC.AppConfig.setValue('brute_force_protection', 'brute_force_protection_ban_period', banPeriod);
				OC.msg.finishedSuccess('#save-bfp-settings-message', t('brute_force_protection', 'Preferences are saved'));
			} else {
				OC.msg.finishedError('#save-bfp-settings-message', t('brute_force_protection', 'Inputs must be positive integers'));
			}
		}
	);
});
