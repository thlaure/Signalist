Feature: Bookmark Management
  As an API consumer
  I want to manage bookmarks
  So that I can save articles for later reading

  Background:
    Given I set the content type to JSON

  Scenario: List bookmarks when none exist
    When I send a "GET" request to "/api/v1/bookmarks"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Delete a non-existent bookmark
    When I send a "DELETE" request to "/api/v1/bookmarks/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
