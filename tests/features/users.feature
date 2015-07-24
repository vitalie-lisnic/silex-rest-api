@f1
Feature: User CRUD
  As a mobile application,
  I want to have the possibility to make CRUD operations over users using REST API

  Scenario: Create user
    Given a list of users in database:
      |id|username|first_name|last_name|email|
    When I make a 'POST' request to '/api/user' with body:
    """
    {"class":"user","properties":{"username":"happy-user","first_name":"Happy", "last_name":"User1","email":"happy_user@test.com"}}
    """
    Then I should get '200' response code
    And response body should be a valid json
    And json response body should contain property 'class' equal to 'user'

  Scenario: Get user
    Given a list of users in database:
      |id|username|first_name|last_name|email|
      |3 |test1   |test1     |test1    |aaa@aaa.com|
      |2 |test2   |test3     |test2    |aaa121@aaa.com|
    When I make a 'GET' request to '/api/user/2'
    Then I should get '200' response code
    And response body should be a valid json
    And json response body should contain property 'class' equal to 'user'
    And json response body should contain property 'properties->username' equal to 'test2'
    And json response body should contain property 'properties->first_name' equal to 'test3'

  Scenario: Update user
    Given a list of users in database:
      |id|username|first_name|last_name|email|
      |3 |test1   |test1     |test1    |aaa@aaa.com|
    When I make a 'PUT' request to '/api/user/3' with body:
    """
    {"class":"user","properties":{"username":"happy-user"}}
    """
    Then I should get '200' response code
    And response body should be a valid json
    And json response body should contain property 'class' equal to 'user'
    And json response body should contain property 'properties->username' equal to 'happy-user'

  @seriy
  Scenario: Delete user
    Given a list of users in database:
      |id|username|first_name|last_name|email|
      |3 |test1   |test1     |test1    |aaa@aaa.com|
    When I make a 'DELETE' request to '/api/user/3'
    Then I should get '200' response code
    And response body should be a valid json
    And json response body should contain property 'class' equal to 'user'
    And json response body should contain property 'properties->username' equal to 'test1'
    When I make a 'GET' request to '/api/user/3'
    Then I should get '404' response code









