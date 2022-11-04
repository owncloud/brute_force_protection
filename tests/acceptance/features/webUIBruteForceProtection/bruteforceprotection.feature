@webUI
Feature: brute force protection

  As an administrator
  I want to be able to lock out users after multiple failed login attempts
  So that the server is protected against brute force password attacks

  Background:
    Given these users have been created without skeleton files:
      | username |
      | Alice    |
    When the administrator sets the bruteforceprotection settings using the webUI to:
      | threshold-time | 60  |
      | fail-tolerance | 2   |
      | ban-period     | 300 |
    And the administrator has logged out of the webUI


  Scenario: valid login works after one false password
    When the user logs in with username "Alice" and invalid password "invalidpassword" using the webUI
    And the user logs in with username "Alice" and password "%regular%" using the webUI
    Then the user should be redirected to a webUI page with the title "Files - ownCloud"


  Scenario: login blocked
    When the user logs in with username "Alice" and invalid password "invalidpassword" using the webUI
    And the user logs in with username "Alice" and invalid password "invalidpassword" using the webUI
    And the blocked user "Alice" tries to login using the password "%regular%" from the webUI
    Then the user should be redirected to a webUI page with the title "ownCloud"
