Feature: Feed Management
  As an API consumer
  I want to manage RSS feeds
  So that I can aggregate content from various sources

  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: List feeds when none exist
    When I send a "GET" request to "/api/v1/feeds"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Create a feed with valid data
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml",
        "categoryId": "stored:categoryId",
        "title": "Example Feed"
      }
      """
    Then the response status code should be 201
    And the response should be JSON
    And the JSON response should contain "id"
    And the JSON response "url" should equal "https://example.com/feed.xml"

  Scenario: Create a feed without a category
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Create a feed with invalid URL
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "not-a-valid-url",
        "categoryId": "stored:categoryId"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Create a feed with non-existent category
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml",
        "categoryId": "00000000-0000-0000-0000-000000000000"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Delete a feed
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I store the response "id" as "feedId"
    When I send a "DELETE" request to "/api/v1/feeds/stored:feedId"
    Then the response status code should be 204

  Scenario: Get a non-existent feed
    When I send a "GET" request to "/api/v1/feeds/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: User cannot see another user's feeds
    Given a category exists with name "Admin Cat" and slug "admin-cat"
    And I store the response "id" as "categoryId"
    And I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/admin-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I am authenticated as "user@signalist.app"
    When I send a "GET" request to "/api/v1/feeds"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: User cannot get another user's feed
    Given a category exists with name "Admin Cat" and slug "admin-cat"
    And I store the response "id" as "categoryId"
    And I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/admin-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I store the response "id" as "feedId"
    And I am authenticated as "user@signalist.app"
    When I send a "GET" request to "/api/v1/feeds/stored:feedId"
    Then the response status code should be 404

  Scenario: Update a feed with valid data
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml",
        "categoryId": "stored:categoryId",
        "title": "Old Title"
      }
      """
    And I store the response "id" as "feedId"
    When I send a "PUT" request to "/api/v1/feeds/stored:feedId" with body:
      """
      {
        "title": "New Title",
        "categoryId": "stored:categoryId",
        "status": "paused"
      }
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "title" should equal "New Title"
    And the JSON response "status" should equal "paused"

  Scenario: Update a feed with non-existent category
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I store the response "id" as "feedId"
    When I send a "PUT" request to "/api/v1/feeds/stored:feedId" with body:
      """
      {
        "title": "New Title",
        "categoryId": "00000000-0000-0000-0000-000000000000"
      }
      """
    Then the response status code should be 422
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Create a feed with duplicate URL
    Given a category exists with name "Tech" and slug "tech"
    And I store the response "id" as "categoryId"
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/duplicate-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    Then the response status code should be 201
    When I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/duplicate-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    Then the response status code should be 409
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: User cannot update another user's feed
    Given a category exists with name "Admin Cat" and slug "admin-cat"
    And I store the response "id" as "categoryId"
    And I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/admin-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I store the response "id" as "feedId"
    And I am authenticated as "user@signalist.app"
    When I send a "PUT" request to "/api/v1/feeds/stored:feedId" with body:
      """
      {
        "title": "Hacked Title",
        "categoryId": "stored:categoryId"
      }
      """
    Then the response status code should be 404

  Scenario: User cannot delete another user's feed
    Given a category exists with name "Admin Cat" and slug "admin-cat"
    And I store the response "id" as "categoryId"
    And I send a "POST" request to "/api/v1/feeds" with body:
      """
      {
        "url": "https://example.com/admin-feed.xml",
        "categoryId": "stored:categoryId"
      }
      """
    And I store the response "id" as "feedId"
    And I am authenticated as "user@signalist.app"
    When I send a "DELETE" request to "/api/v1/feeds/stored:feedId"
    Then the response status code should be 404
