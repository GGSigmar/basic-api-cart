<?php

namespace App\Test;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiTestCase extends WebTestCase
{
    private static $staticClient;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$staticClient = self::createClient();

        self::bootKernel();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->client = self::$staticClient;

        $this->urlGenerator = $this->getService(UrlGeneratorInterface::class);

        $this->purgeDatabase();
    }

    public function tearDown(): void
    {
        $this->getEntityManager()->clear();
    }

    /**
     * @param $id
     * @return object|null
     */
    protected function getService($id)
    {
        return self::$container->get($id);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getService('doctrine')->getManager();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string $content
     */
    protected function apiRequest(string $method, string $uri, string $content = '')
    {
        $this->client->request($method, $uri, [], [], [], $content);
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }
}