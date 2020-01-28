@api
Feature: brute force protection

  As an administrator
  I want to be able to lock out users after multiple failed login attempts
  So that the server is protected against brute force password attacks

  Background:
	Given these users have been created with skeleton files:
	  | username |
	  | user1    |
	And the administrator has set the bruteforceprotection settings to:
	  | threshold-time | 60  |
	  | fail-tolerance | 2   |
	  | ban-period     | 300 |

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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

  Scenario Outline: access is still possible from another IP after user/ip combination was blocked
	When the client accesses the server from IP address "105.153.70.216" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>" with password "notvalid"
	And the client accesses the server from IP address "85.126.184.17" using X-Forwarded-For header
	And user "user1" sends HTTP method "<method>" to URL "<endpoint>"
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
