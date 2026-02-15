Feature: Bookmark Management
  As an API consumer
  I want to manage bookmarks
  So that I can save articles for later reading

  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

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

  Scenario: Create a bookmark
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId",
        "notes": "Interesting article"
      }
      """
    Then the response status code should be 201
    And the response should be JSON
    And the JSON response should contain "id"
    And the JSON response "articleTitle" should equal "Test Article"
    And the JSON response "notes" should equal "Interesting article"

  Scenario: Create a duplicate bookmark
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId"
      }
      """
    Then the response status code should be 201
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId"
      }
      """
    Then the response status code should be 409
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Get a bookmark
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId"
      }
      """
    And the response should be JSON
    And I store the response "id" as "bookmarkId"
    When I send a "GET" request to "/api/v1/bookmarks/stored:bookmarkId"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "articleTitle" should equal "Test Article"

  Scenario: User cannot see another user's bookmarks
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId"
      }
      """
    And I am authenticated as "user@signalist.app"
    When I send a "GET" request to "/api/v1/bookmarks"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: User cannot delete another user's bookmark
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "POST" request to "/api/v1/bookmarks" with body:
      """
      {
        "articleId": "stored:articleId"
      }
      """
    And the response should be JSON
    And I store the response "id" as "bookmarkId"
    And I am authenticated as "user@signalist.app"
    When I send a "DELETE" request to "/api/v1/bookmarks/stored:bookmarkId"
    Then the response status code should be 404
