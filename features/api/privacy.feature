Feature: Account privacy controls
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: Read own profile
    When I send a "GET" request to "/api/v1/user/profile"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "email" should equal "admin@signalist.app"

  Scenario: Reject unknown consent purpose
    When I send a "POST" request to "/api/v1/user/consent" with body:
      """
      {"purpose":"anything", "granted":true}
      """
    Then the response status code should be 422

  Scenario: Restrict processing while retaining access to privacy controls
    When I send a "POST" request to "/api/v1/user/restrict-processing" with body:
      """
      {"restricted":true}
      """
    Then the response status code should be 204
    When I send a "GET" request to "/api/v1/user/profile"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "processingRestricted" should equal "true"

  Scenario: Require password confirmation before deleting account
    When I send a "DELETE" request to "/api/v1/user/account" with body:
      """
      {"password":"wrong"}
      """
    Then the response status code should be 403

  Scenario: Export own data
    When I send a "GET" request to "/api/v1/user/data-export?format=portable"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response should contain "profile"
    And the JSON response should contain "bookmarks"
