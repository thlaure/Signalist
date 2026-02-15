Feature: Authentication - Login
  As an API consumer
  I want to authenticate with my credentials
  So that I can access protected resources

  Background:
    Given I set the content type to JSON
    And there are default users

  Scenario: Successful login with valid credentials
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "admin@signalist.app",
        "password": "password"
      }
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response should contain "token"
    And the JSON response should contain "expiresIn"
    And the JSON response "expiresIn" should equal "3600"

  Scenario: Login with wrong password
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "admin@signalist.app",
        "password": "wrongpassword"
      }
      """
    Then the response status code should be 401
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with non-existent user
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "unknown@signalist.app",
        "password": "password123"
      }
      """
    Then the response status code should be 401
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with missing email
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "password": "password123"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with missing password
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "admin@signalist.app"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with invalid email format
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "not-an-email",
        "password": "password123"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with password too short
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "admin@signalist.app",
        "password": "short"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Login with unverified email
    Given there is an unverified user "unverified@signalist.app" with password "password"
    When I send a "POST" request to "/api/v1/auth/login" with body:
      """
      {
        "email": "unverified@signalist.app",
        "password": "password"
      }
      """
    Then the response status code should be 403
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
