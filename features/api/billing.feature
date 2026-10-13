Feature: Billing remains private and fails clearly until configured
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: Read default free plan limits
    When I send a "GET" request to "/api/v1/billing"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "plan" should equal "free"
    And the JSON response "configured" should equal "false"

  Scenario: Checkout requires complete provider configuration
    When I send a "POST" request to "/api/v1/billing/links" with body:
      """
      {"kind":"checkout"}
      """
    Then the response status code should be 503

  Scenario: Arbitrary billing link identifiers do not expose URLs
    When I send a "GET" request to "/api/v1/billing/links/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
