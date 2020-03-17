@api
Feature: brute force protection

  As an administrator
  I want to be able to lock out users after multiple failed login attempts
  So that the server is protected against brute force password attacks

  Background:
    Given these users have been created with skeleton files:
      | username |
      | Alice    |
    And the administrator has set the bruteforceprotection settings to:
      | threshold-time | 60  |
      | fail-tolerance | 2   |
      | ban-period     | 300 |

  Scenario Outline: access is blocked after too many invalid requests
    When user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 403       |
      | PROPFIND | /remote.php/dav/systemtags              | 401       |
      | PROPFIND | /remote.php/dav/files/Alice/welcome.txt | 401       |
      | GET      | /remote.php/dav/files/Alice/welcome.txt | 401       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 401       |
      | GET      | /remote.php/webdav/welcome.txt          | 401       |
      | MKCOL    | /remote.php/dav/files/Alice/blocked     | 401       |
      | MKCOL    | /remote.php/webdav/blocked              | 401       |

  Scenario Outline: access is still possible if the invalid requests did not reach fail-tolerance
    When user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/Alice/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/Alice/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/Alice/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario Outline: access is still possible as another user after a user was blocked
    Given these users have been created with skeleton files:
      | username |
      | Brian    |
    When user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Brian" sends HTTP method "<method>" to URL "<endpoint>"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/Brian/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/Brian/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/Brian/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
    When the client accesses the server from IP address "10.4.1.248" using X-Forwarded-For header
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
    And the client accesses the server from IP address "192.168.56.1" using X-Forwarded-For header
    And user "Alice" sends HTTP method "<method>" to URL "<endpoint>"
    Then the HTTP status code should be "<http-code>"
    Examples:
      | method   | endpoint                                | http-code |
      | GET      | /index.php/apps/files                   | 200       |
      | PROPFIND | /remote.php/dav/systemtags              | 207       |
      | PROPFIND | /remote.php/dav/files/Alice/welcome.txt | 207       |
      | GET      | /remote.php/dav/files/Alice/welcome.txt | 200       |
      | PROPFIND | /remote.php/webdav/welcome.txt          | 207       |
      | GET      | /remote.php/webdav/welcome.txt          | 200       |
      | MKCOL    | /remote.php/dav/files/Alice/blocked     | 201       |
      | MKCOL    | /remote.php/webdav/blocked              | 201       |

  Scenario: accessing different endpoints with wrong password should block user
    When user "Alice" sends HTTP method "PROPFIND" to URL "/remote.php/dav/systemtags" with password "notvalid"
    And user "Alice" sends HTTP method "GET" to URL "/remote.php/webdav/welcome.txt" with password "notvalid"
    And user "Alice" sends HTTP method "GET" to URL "/index.php/apps/files"
    Then the HTTP status code should be "403"

  Scenario: access to public link is not blocked after too many invalid requests
    Given user "user1" has uploaded file with content "user1 file" to "/PARENT/randomfile.txt"
    When user "user1" creates a public link share using the sharing API with settings
      | path        | PARENT   |
      | password    | %public% |
    And the public download of the last publicly shared file using the new public WebDAV API with password "12345" should fail with HTTP status code "401"
    And the public download of the last publicly shared file using the new public WebDAV API with password "12345" should fail with HTTP status code "401"
    And the public download of the last publicly shared file using the new public WebDAV API with password "123455" should fail with HTTP status code "401"
    And the public should be able to download file "/randomfile.txt" from inside the last public shared folder using the new public WebDAV API with password "%public%" and the content should be "user1 file"
