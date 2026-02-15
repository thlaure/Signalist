Feature: Authentication - Register
  As a new user
  I want to create an account
  So that I can access the platform

  Background:
    Given I set the content type to JSON
    And there are default users

  Scenario: Successful registration
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "email": "newuser@signalist.app",
        "password": "securepassword"
      }
      """
    Then the response status code should be 201
    And the response should be JSON
    And the JSON response should contain "id"

  Scenario: Registration with duplicate email
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "email": "admin@signalist.app",
        "password": "securepassword"
      }
      """
    Then the response status code should be 409
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Registration with missing email
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "password": "securepassword"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Registration with missing password
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "email": "newuser@signalist.app"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Registration with invalid email format
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "email": "not-an-email",
        "password": "securepassword"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Registration with password too short
    When I send a "POST" request to "/api/v1/auth/register" with body:
      """
      {
        "email": "newuser@signalist.app",
        "password": "short"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
