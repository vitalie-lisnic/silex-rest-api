<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

use Symfony\Component\HttpKernel\Client;

require_once __DIR__ . '/../../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{

    protected $requestMethod;
    protected $app;
    /**
     * @var Client
     */
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
        $this->client = new Client($this->app);
    }

    /**
     * @Given user list in database:
     */
    public function userListInDatabase(TableNode $table)
    {
        $this->app['db']->query("TRUNCATE users;");
        foreach ($table as $row) {
            $this->app['userService']->createUser($row);
        }
    }

    /**
     * @When request method is :arg1
     */
    public function requestMethodIs($arg1)
    {
        $this->requestMethod = $arg1;
    }

    /**
     * @When I make a request to :arg1
     */
    public function iMakeARequestTo($arg1)
    {
        $this->client->request($this->requestMethod, $arg1);
    }

    /**
     * @Then response code should be :arg1
     */
    public function responseCodeShouldBe($arg1)
    {
        assertEquals($arg1, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @Then response body should contain a valid json response
     */
    public function responseBodyShouldContainAValidJsonResponse()
    {
        assertJson($this->client->getResponse()->getContent());
    }

    /**
     * @Then json response should contain property :arg1 with value :arg2
     */
    public function jsonResponseShouldContainPropertyWithValue($arg1, $arg2)
    {
        $responseBody = json_decode($this->client->getResponse()->getContent(), true);

        $nestedProperties = explode('->', $arg1);

        $foundValue = $responseBody;
        foreach ($nestedProperties as $property) {
            assertArrayHasKey($property, $foundValue);
            $foundValue = $foundValue[$property];
        }
        assertEquals($foundValue, $arg2);

    }

}
