Feature: User CRUD
  As a client application,
  I need to perform CRUD operations over users using REST API

  Scenario: Extract user data from REST API
    Given user list in database:
      |id|username     |first_name|last_name|email        |
      |1 |happy-user   |Happy     |User     |test@test.com|
    When request method is 'GET'
    And I make a request to '/api/user/1'
    Then response code should be '200'
    And response body should contain a valid json response
    And json response should contain property 'class' with value 'user'
    And json response should contain property 'properties->id' with value '1'
    And json response should contain property 'properties->username' with value 'happy-user'
    And json response should contain property 'properties->first_name' with value 'Happy'
    And json response should contain property 'properties->last_name' with value 'User'
    And json response should contain property 'properties->email' with value 'test@test.com'
