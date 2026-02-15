Feature: Authentication - Email Verification
  As a registered user
  I want to verify my email address
  So that I can log in to the platform

  Background:
    Given I set the content type to JSON
    And there are default users

  Scenario: Verify email with invalid signature
    When I send a "POST" request to "/api/v1/auth/verify-email" with body:
      """
      {
        "userId": "550e8400-e29b-41d4-a716-446655440000",
        "email": "test@signalist.app",
        "expiresAt": 9999999999,
        "signature": "invalid-signature"
      }
      """
    Then the response status code should be 400
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Verify email with missing fields
    When I send a "POST" request to "/api/v1/auth/verify-email" with body:
      """
      {
        "userId": "550e8400-e29b-41d4-a716-446655440000"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Resend verification always returns 200
    When I send a "POST" request to "/api/v1/auth/resend-verification" with body:
      """
      {
        "email": "nonexistent@signalist.app"
      }
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "sent" should equal "true"

  Scenario: Resend verification with missing email
    When I send a "POST" request to "/api/v1/auth/resend-verification" with body:
      """
      {}
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
