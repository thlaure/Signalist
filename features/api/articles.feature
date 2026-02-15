Feature: Article Management
  As an API consumer
  I want to browse and manage articles
  So that I can read content from my feeds

  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: List articles when none exist
    When I send a "GET" request to "/api/v1/articles"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: Get a non-existent article
    When I send a "GET" request to "/api/v1/articles/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404
    And the response should be JSON
    And the JSON response should be a RFC 7807 problem

  Scenario: Mark an article as read
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "PATCH" request to "/api/v1/articles/stored:articleId/read" with body:
      """
      {}
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "isRead" should equal "true"

  Scenario: Mark an article as unread
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    When I send a "PATCH" request to "/api/v1/articles/stored:articleId/read" with body:
      """
      {}
      """
    Then the response status code should be 200
    When I send a "PATCH" request to "/api/v1/articles/stored:articleId/unread" with body:
      """
      {}
      """
    Then the response status code should be 200
    And the response should be JSON
    And the JSON response "isRead" should equal "false"

  Scenario: User cannot see another user's articles
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I am authenticated as "user@signalist.app"
    When I send a "GET" request to "/api/v1/articles"
    Then the response status code should be 200
    And the response should be JSON
    And the JSON collection should be empty

  Scenario: User cannot mark another user's article as read
    Given a category exists with name "Tech" and slug "tech"
    And a feed exists with title "Tech Feed" and URL "https://example.com/feed.xml" in category "tech"
    And an article exists with title "Test Article" in feed "Tech Feed"
    And I store the last article ID as "articleId"
    And I am authenticated as "user@signalist.app"
    When I send a "PATCH" request to "/api/v1/articles/stored:articleId/read" with body:
      """
      {}
      """
    Then the response status code should be 404
