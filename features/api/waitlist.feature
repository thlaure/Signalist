Feature: Double opt-in waitlist
  Background:
    Given I set the content type to JSON

  Scenario: Accept valid request without revealing enrollment status
    When I send a "POST" request to "/api/v1/waitlist" with body:
      """
      {"email":"waitlist@example.com", "consent":true}
      """
    Then the response status code should be 202
    And the response should be JSON
    And the JSON response should contain "message"

  Scenario: Consent is mandatory
    When I send a "POST" request to "/api/v1/waitlist" with body:
      """
      {"email":"waitlist@example.com", "consent":false}
      """
    Then the response status code should be 422

  Scenario: Email must be valid
    When I send a "POST" request to "/api/v1/waitlist" with body:
      """
      {"email":"invalid", "consent":true}
      """
    Then the response status code should be 422

  Scenario: Forged withdrawal link is rejected
    When I send a "POST" request to "/api/v1/waitlist/00000000-0000-0000-0000-000000000000/withdraw?signature=forged" with body:
      """
      {}
      """
    Then the response status code should be 400
