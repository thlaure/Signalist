Feature: Connected accounts remain unavailable until configured
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: List connection readiness without exposing credentials
    When I send a "GET" request to "/api/v1/connections"
    Then the response status code should be 200
    And the response should be JSON

  Scenario: Authorization requires complete provider configuration
    When I send a "POST" request to "/api/v1/connections/x/authorize" with body:
      """
      {}
      """
    Then the response status code should be 503

  Scenario: Disconnect cannot target an arbitrary account
    When I send a "DELETE" request to "/api/v1/connections/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404

  Scenario: A forged OAuth callback is rejected
    When I send a "GET" request to "/api/v1/connections/x/callback?code=forged&state=forged"
    Then the response status code should be 400
