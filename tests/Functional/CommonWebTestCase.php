<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\Bundle\FixturesBundle\Purger\ORMPurgerFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Throwable;

use function array_merge;
use function get_class;
use function in_array;

abstract class CommonWebTestCase extends WebTestCase
{
    /**
     * The setUp before class
     *
     * @return mixed
     */
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();
    }

    protected function tearDown(): void
    {
        static::ensureKernelShutdown();
    }

    /**
     * Create schema before test
     */
    public static function databaseSchemaCreate(): void
    {
        static $databaseCreated = false;
        if (! $databaseCreated) {
            static::createDatabase();
            $databaseCreated = true;
        }

        static::bootKernel();

        $em = static::$container->get(EntityManagerInterface::class);
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($em);
        try {
            $tool->createSchema($metadatas);
        } catch (Throwable $e) {
            echo $e->getMessage();
        }

        static::ensureKernelShutdown();
    }

    /**
     * Drop schema
     */
    public static function databaseSchemaDrop(): void
    {
        static::bootKernel();

        $em = static::$container->get(EntityManagerInterface::class);
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($em);
        try {
            $tool->dropSchema($metadatas);
        } catch (Throwable $e) {
            echo $e->getMessage();
        }

        static::ensureKernelShutdown();
    }

    /**
     * Load the fixture
     */
    public static function loadFixtures(): void
    {
        static::bootKernel();

        $em = static::$container->get(EntityManagerInterface::class);
        $loader = self::$container->get('doctrine.fixtures.loader');
        $fixtures = $loader->getFixtures();

        $factory = new ORMPurgerFactory();
        $purger = $factory->createForEntityManager(null, $em, [], false);

        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures);

        static::ensureKernelShutdown();
    }

    /**
     * The invoke method function
     *
     * @param mixed $object     The object
     * @param mixed $methodName The methode name
     * @param mixed[] $parameters The parameters
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * This is used to override a private var
     *
     * @param object $object   : Is the class name
     * @param string $variable : Is the name of the var do you want to override
     * @param mixed  $value    : Value to override
     *
     * @throws ReflectionException
     */
    public function overridePrivateVar(object $object, string $variable, $value): void
    {
        $reflector = new ReflectionProperty(get_class($object), $variable);
        $reflector->setAccessible(true);
        $reflector->setValue($object, $value);
    }

    private static function createDatabase(): void
    {
        static::bootKernel();
        $doctrine = static::$container->get('doctrine');
        $connection = $doctrine->getConnection();

        $driverOptions = [];
        $params        = $connection->getParams();

        if (isset($params['driverOptions'])) {
            $driverOptions = $params['driverOptions'];
        }

        // Since doctrine/dbal 2.11 master has been replaced by primary
        if (isset($params['primary'])) {
            $params                  = $params['primary'];
            $params['driverOptions'] = $driverOptions;
        }

        if (isset($params['master'])) {
            $params                  = $params['master'];
            $params['driverOptions'] = $driverOptions;
        }

        // Cannot inject `shard` option in parent::getDoctrineConnection
        // cause it will try to connect to a non-existing database
        if (isset($params['shards'])) {
            // Default select global
            $params = array_merge($params, $params['global']);
            unset($params['global']['dbname'], $params['global']['path'], $params['global']['url']);
        }

        $hasPath = isset($params['path']);
        $name    = $hasPath ? $params['path'] : ($params['dbname'] ?? false);
        if (! $name) {
            throw new InvalidArgumentException(
                "Connection does not contain a 'path' or 'dbname' parameter and cannot be created."
            );
        }

        // Need to get rid of _every_ occurrence of dbname from connection configuration and we
        // have already extracted all relevant info from url
        unset($params['dbname'], $params['path'], $params['url']);

        $tmpConnection = DriverManager::getConnection($params);
        $tmpConnection->connect();
        $shouldNotCreateDatabase = in_array($name, $tmpConnection->getSchemaManager()->listDatabases(), true);

        try {
            if (! $shouldNotCreateDatabase) {
                $tmpConnection->getSchemaManager()->createDatabase($name);
            }
        } catch (Throwable $e) {
            echo "something went wrong on database creation\n";
            echo $e->getMessage();
        } finally {
            $tmpConnection->close();
        }
    }
}
