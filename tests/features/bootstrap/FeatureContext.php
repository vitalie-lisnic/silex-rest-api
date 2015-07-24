<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

require_once __DIR__ . '/../../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{

    protected $app;
    protected $client;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->app = include __DIR__ . '/../../../bootstrap.php';
        $this->client = new \Symfony\Component\HttpKernel\Client($this->app);
    }

    /**
     * @Given a list of users in database:
     */
    public function aListOfUsersInDatabase(TableNode $table)
    {
        $this->app['db']->query('TRUNCATE users;');
        foreach($table as $row){
            $this->app['userService']->createUser($row);
        }
    }

    /**
     * @When I make a :arg1 request to :arg2 with body:
     * @When I make a :arg1 request to :arg2
     */
    public function iMakeARequestToWithBody($arg1, $arg2, PyStringNode $string = null)
    {
        $this->client->request($arg1, $arg2, [], [],[], $string);
    }


    /**
     * @Then I should get :arg1 response code
     */
    public function iShouldGetResponseCode($arg1)
    {
        assertEquals($arg1, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @Then response body should be a valid json
     */
    public function responseBodyShouldBeAValidJson()
    {
        assertJson($this->client->getResponse()->getContent());
    }

    /**
     * @Then json response body should contain property :arg1 equal to :arg2
     */
    public function jsonResponseBodyShouldContainPropertyEqualTo($arg1, $arg2)
    {
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $nestedProperties = explode('->', $arg1);

        $foundValue = $responseData;
        foreach($nestedProperties as $property) {
            assertArrayHasKey($property, $foundValue);
            $foundValue = $foundValue[$property];
        }
        assertEquals($arg2, $foundValue);

    }

}
