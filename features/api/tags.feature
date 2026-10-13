Feature: Owner-scoped tag catalog
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: Create a tag
    When I send a "POST" request to "/api/v1/tags" with body:
      """
      {"name":"Technology"}
      """
    Then the response status code should be 201
    And the JSON response "name" should equal "Technology"

  Scenario: Empty tag names fail validation
    When I send a "POST" request to "/api/v1/tags" with body:
      """
      {"name":""}
      """
    Then the response status code should be 422

  Scenario: Foreign tag identifiers cannot expose articles
    When I send a "GET" request to "/api/v1/tags/00000000-0000-0000-0000-000000000000/articles"
    Then the response status code should be 404
