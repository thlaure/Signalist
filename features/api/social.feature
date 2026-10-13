Feature: Private social drafts and explicitly approved publishing
  Background:
    Given I set the content type to JSON
    And there are default users
    And I am authenticated as "admin@signalist.app"

  Scenario: A curated recap can be queued without an AI provider
    When I send a "POST" request to "/api/v1/social-recaps" with body:
      """
      {"platform":"linkedin","start":"2026-01-01","end":"2026-01-07","useAi":false}
      """
    Then the response status code should be 202
    And the JSON response "status" should equal "pending"

  Scenario: Publishing requires explicit confirmation
    When I send a "POST" request to "/api/v1/crossposts" with body:
      """
      {"accountId":"00000000-0000-0000-0000-000000000000","text":"Review first","confirmed":false}
      """
    Then the response status code should be 422

  Scenario: Arbitrary recap identifiers expose no drafts
    When I send a "GET" request to "/api/v1/social-recaps/00000000-0000-0000-0000-000000000000"
    Then the response status code should be 404

  Scenario: Unsupported platforms are rejected
    When I send a "POST" request to "/api/v1/social-recaps" with body:
      """
      {"platform":"unknown","start":"2026-01-01","end":"2026-01-07"}
      """
    Then the response status code should be 422
