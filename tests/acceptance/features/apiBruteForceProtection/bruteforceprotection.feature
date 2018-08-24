@api
Feature: brute force protection

As an administrator
I want to be able to lock out users after multiple failed login attempts
So that the server is protected against brute force password attacks

	Background:
		Given using OCS API version "2"
		Given these users have been created:
			|username|password|displayname|email       |
			|user1   |1234    |User One   |u1@oc.com.np|
		And the administrator has set the bruteforceprotection settings to:
			| threshold-time | 60    |
			| fail-tolerance | 2     |
			| ban-period     | 300   |

	Scenario: access to files app is blocked after too many invalid requests
		When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "notvalid"
		When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "notvalid"
		When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "1234"
		And the HTTP status code should be "403"

	Scenario: access to files app is still possible if the invalid requests did not reach fail-tolerance
		When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "notvalid"
		When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "1234"
		And the HTTP status code should be "200"
