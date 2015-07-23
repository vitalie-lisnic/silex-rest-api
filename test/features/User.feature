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


  Scenario: Extract list of all registered user ids
    Given user list in database:
      |id |username     |first_name |last_name |email         |
      |21 |happy-user   |Happy      |User      |test@test.com |
      |20 |happy-user2  |Happy2     |User2     |test2@test.com|
    When request method is 'GET'
    And I make a request to '/api/user'
    Then response code should be '200'
    And json response should contain property 'class' with value 'collection'
    And json response should contain property 'properties->0' with value '20'
    And json response should contain property 'properties->1' with value '21'

  Scenario: Create user
    Given user list in database:
      |id |username     |first_name |last_name |email|
    When request method is 'POST'
    And request body is:
    """
    {"class":"user","properties":{"username":"testuser3","first_name":"Test 3","last_name":"User 3","email":"test3@aaa.com"}}
    """
    And I make a request to '/api/user'
    Then response code should be '200'
    And json response should contain property 'class' with value 'user'
    And json response should contain property 'properties->username' with value 'testuser3'
    And json response should contain property 'properties->first_name' with value 'Test 3'
    And json response should contain property 'properties->last_name' with value 'User 3'
    And json response should contain property 'properties->email' with value 'test3@aaa.com'

  Scenario: Update user
    Given user list in database:
      |id |username     |first_name |last_name |email         |
      |21 |happy-user   |Happy      |User      |test@test.com |
    When request method is 'PUT'
    And request body is:
    """
    {"class":"user","properties":{"username":"testuser3","first_name":"Test 3","last_name":"User 3","email":"test3@aaa.com"}}
    """
    And I make a request to '/api/user/21'
    Then response code should be '200'
    And json response should contain property 'class' with value 'user'
    And json response should contain property 'properties->id' with value '21'
    And json response should contain property 'properties->username' with value 'testuser3'
    And json response should contain property 'properties->first_name' with value 'Test 3'
    And json response should contain property 'properties->last_name' with value 'User 3'
    And json response should contain property 'properties->email' with value 'test3@aaa.com'

  Scenario: Delete user
    Given user list in database:
      |id |username     |first_name |last_name |email         |
      |21 |happy-user   |Happy      |User      |test@test.com |
    When request method is 'DELETE'
    And I make a request to '/api/user/21'
    Then response code should be '200'
    And json response should contain property 'class' with value 'user'
    And json response should contain property 'properties->id' with value '21'
    And json response should contain property 'properties->username' with value 'happy-user'
    And json response should contain property 'properties->first_name' with value 'Happy'
    And json response should contain property 'properties->last_name' with value 'User'
    And json response should contain property 'properties->email' with value 'test@test.com'
    But If set request method to 'GET'
    And If I make a request to '/api/user/21'
    Then response code should be '404'