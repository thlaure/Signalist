Feature: Spotlight commands
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: Create category using command
    When I send a "POST" request to "/api/v1/spotlight" with body:
      """
      {"command":"create category Technology"}
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response should contain "path"

  Scenario: Unsafe feed command cannot mutate data
    When I send a "POST" request to "/api/v1/spotlight" with body:
      """
      {"command":"add feed http://127.0.0.1/private in Tech"}
      """
    Then the response status code should be 400

  Scenario: Unknown command becomes a search
    When I send a "POST" request to "/api/v1/spotlight" with body:
      """
      {"command":"quantum computing"}
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "path" should equal "/search?q=quantum%20computing"
