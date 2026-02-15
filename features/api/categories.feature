Feature: Category Management
  As an API consumer
  I want to manage categories
  So that I can organize my RSS feeds

  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: List categories when none exist
    When I send a "GET" request to "/api/v1/categories"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Create a category with valid data
    When I send a "POST" request to "/api/v1/categories" with body:
      """
      {
        "name": "Technology",
        "slug": "technology",
        "description": "Tech news and articles",
        "color": "#3498db"
      }
      """
    Then the response status code should be 201
    And the response should be JSON
    And the JSON response should contain "id"
    And the JSON response "name" should equal "Technology"
    And the JSON response "slug" should equal "technology"

  Scenario: Create a category with minimal data
    When I send a "POST" request to "/api/v1/categories" with body:
      """
      {
        "name": "News",
        "slug": "news"
      }
      """
    Then the response status code should be 201
    And the response should be JSON
    And the JSON response should contain a valid UUID in "id"

  Scenario: Create a category with missing required fields
    When I send a "POST" request to "/api/v1/categories" with body:
      """
      {
        "description": "Missing name and slug"
      }
      """
    Then the response status code should be 500
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Create a category with duplicate slug
    Given a category exists with name "Tech" and slug "tech"
    When I send a "POST" request to "/api/v1/categories" with body:
      """
      {
        "name": "Technology",
        "slug": "tech"
      }
      """
    Then the response status code should be 409
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: List categories after creating one
    Given a category exists with name "Science" and slug "science"
    When I send a "GET" request to "/api/v1/categories"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should have 1 items

  Scenario: Update a category
    Given a category exists with name "Old Name" and slug "old-name"
    And I store the response "id" as "categoryId"
    When I send a "PUT" request to "/api/v1/categories/stored:categoryId" with body:
      """
      {
        "name": "New Name",
        "slug": "new-name"
      }
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "name" should equal "New Name"

  Scenario: Delete a category
    Given a category exists with name "To Delete" and slug "to-delete"
    And I store the response "id" as "categoryId"
    When I send a "DELETE" request to "/api/v1/categories/stored:categoryId"
    Then the response status code should be 204

  Scenario: Get a non-existent category
    When I send a "GET" request to "/api/v1/categories/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem
