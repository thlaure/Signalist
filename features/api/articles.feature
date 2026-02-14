Feature: Article Management
  As an API consumer
  I want to browse and manage articles
  So that I can read content from my feeds

  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: List articles when none exist
    When I send a "GET" request to "/api/v1/articles"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Get a non-existent article
    When I send a "GET" request to "/api/v1/articles/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
