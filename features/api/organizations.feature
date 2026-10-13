Feature: Shared workspace permissions
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: Create workspace and category
    When I send a "POST" request to "/api/v1/organizations" with body:
      """
      {"name":"Editorial"}
      """
    Then the response status code should be 201
    And the response should be JSON
    And I store the response "id" as "workspaceId"
    When I send a "POST" request to "/api/v1/organizations/stored:workspaceId/categories" with body:
      """
      {"name":"Tech", "slug":"tech"}
      """
    Then the response status code should be 201
    When I send a "GET" request to "/api/v1/categories"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Nonmember cannot read workspace
    When I send a "POST" request to "/api/v1/organizations" with body:
      """
      {"name":"Editorial"}
      """
    Then the response status code should be 201
    And the response should be JSON
    And I store the response "id" as "workspaceId"
    Given I am authenticated as "user@signalist.app"
    When I send a "GET" request to "/api/v1/organizations/stored:workspaceId/articles"
    Then the response status code should be 404
