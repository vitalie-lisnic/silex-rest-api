Feature: User CRUD
  As a client application,
  I want to perform CRUD operations with users using REST API

  Scenario: Get list of registered user ids
    Given a list of registered users in database:
      |id |username  |first_name |last_name |email        |
      |2  |testuser1 | test1     |test1     |test1@aaa.com|
      |1  |testuser2 | test2     |test2     |test1@aaa.com|
    When I make a 'GET' request to '/api/user'
    Then I should get '200' response code
    And response should be a valid json response
    And json response should contain property 'class' equal to 'collection'
    And json response should contain property 'properties->0' equal to '1'
    And json response should contain property 'properties->1' equal to '2'

  Scenario: Get user by id
    Given a list of registered users in database:
      |id |username  |first_name |last_name |email        |
      |2  |testuser1 | test1     |test1     |test1@aaa.com|
    When I make a 'GET' request to '/api/user/2'
    Then I should get '200' response code
    And response should be a valid json response
    And json response should contain property 'class' equal to 'user'
    And json response should contain property 'properties->username' equal to 'testuser1'
    And json response should contain property 'properties->first_name' equal to 'test1'
    And json response should contain property 'properties->last_name' equal to 'test1'
    And json response should contain property 'properties->email' equal to 'test1@aaa.com'

  Scenario: Get a non-existent user:
    Given a list of registered users in database:
      |id |username  |first_name |last_name |email|
    When I make a 'GET' request to '/api/user/2'
    Then I should get '404' response code
    And response should be a valid json response
    And json response should contain property 'class' equal to 'error'

  Scenario: Create user
    Given a list of registered users in database:
      |id |username  |first_name |last_name |email|
    When I make a 'POST' request to '/api/user' using the following body contents:
    """
    {"class":"user","properties":{"username":"happy-user","first_name":"Happy", "last_name":"User1","email":"happy_user@test.com"}}
    """
    Then I should get '200' response code
    And response should be a valid json response
    And json response should contain property 'class' equal to 'user'
    And json response should contain property 'properties->username' equal to 'happy-user'
    And json response should contain property 'properties->first_name' equal to 'Happy'
    And json response should contain property 'properties->last_name' equal to 'User1'
    And json response should contain property 'properties->email' equal to 'happy_user@test.com'
