@api
Feature: brute force protection

  As an administrator
  I want to be able to lock out users after multiple failed login attempts
  So that the server is protected against brute force password attacks

  Background:
    Given these users have been created:
      | username | password | displayname | email        |
      | user1    | 1234     | User One    | u1@oc.com.np |
    And the administrator has set the bruteforceprotection settings to:
      | threshold-time | 60  |
      | fail-tolerance | 2   |
      | ban-period     | 300 |

  Scenario Outline: access is blocked after too many invalid requests
    Given user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    When user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "1234"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 403       |
      | PROPFIND | /remote.php/dav/systemtags              | 401       |
      | PROPFIND | /remote.php/dav/files/user1/welcome.txt | 401       |
      | GET      | /remote.php/dav/files/user1/welcome.txt | 401       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 401       |
      | GET      | /remote.php/webdav/welcome.txt          | 401       |
      | MKCOL    | /remote.php/dav/files/user1/blocked     | 401       |
      | MKCOL    | /remote.php/webdav/blocked              | 401       |

  Scenario Outline: access is still possible if the invalid requests did not reach fail-tolerance
    Given user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    When user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "1234"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/user1/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/user1/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/user1/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario Outline: access is still possible from as an other user after a user was blocked
    Given these users have been created:
      | username | password | displayname | email        |
      | user2    | 1234     | User Two    | u2@oc.com.np |
    And user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    When user "user2" sends HTTP method "<method>" to URL "<endpoint>" with password "1234"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/user2/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/user2/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/user2/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario Outline: access is still possible from an other IP after user/ip combination was blocked
    Given the client accesses the server from IP address "10.4.1.248" using X-Forwarded-For header
    And user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "user1" has sent HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    When the client accesses the server from IP address "192.168.56.1" using X-Forwarded-For header
    And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "1234"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/user1/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/user1/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/user1/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario: accessing different endpoints with wrong password should block user
    Given user "user1" has sent HTTP method "PROPFIND" to URL "/remote.php/dav/systemtags" with password "notvalid"
    And user "user1" has sent HTTP method "GET" to URL "/remote.php/webdav/welcome.txt" with password "notvalid"
    When user "user1" sends HTTP method "GET" to URL "/index.php/apps/files" with password "1234"
    Then the HTTP status code should be "403"
