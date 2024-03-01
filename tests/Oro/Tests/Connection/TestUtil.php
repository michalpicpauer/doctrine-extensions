<?php
declare(strict_types=1);

namespace Oro\Tests\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;

class TestUtil
{
    /** @var EntityManager */
    private static $entityManager;

    /**
     * @throws \RuntimeException
     * @throws ORMException
     */
    public static function getEntityManager(): EntityManager
    {
        if (!self::$entityManager && self::hasRequiredConnectionParams()) {
            $dbParams = self::getConnectionParams();
            $entitiesPath = \realpath(__DIR__ . '/../../Entities');

            $config = ORMSetup::createAttributeMetadataConfiguration([$entitiesPath], true);
            self::$entityManager = new EntityManager(
                new Connection(
                    $dbParams,
                    new Driver\PDO\PgSQL\Driver()
                ),
                $config
            );
        }

        if (self::$entityManager) {
            return self::$entityManager;
        }

        throw new \RuntimeException('Database connection not configured');
    }

    private static function hasRequiredConnectionParams(): bool
    {
        return isset(
            $GLOBALS['db_type'],
            $GLOBALS['db_username'],
            $GLOBALS['db_password'],
            $GLOBALS['db_host'],
            $GLOBALS['db_name'],
            $GLOBALS['db_port']
        );
    }

    private static function getConnectionParams(): array
    {
        $connectionParams = [
            'driver' => $GLOBALS['db_type'],
            'user' => $GLOBALS['db_username'],
            'password' => $GLOBALS['db_password'],
            'host' => $GLOBALS['db_host'],
            'dbname' => $GLOBALS['db_name'],
            'port' => $GLOBALS['db_port']
        ];

        if (isset($GLOBALS['db_server'])) {
            $connectionParams['server'] = $GLOBALS['db_server'];
        }

        if (isset($GLOBALS['db_unix_socket'])) {
            $connectionParams['unix_socket'] = $GLOBALS['db_unix_socket'];
        }

        return $connectionParams;
    }

    /**
     * @throws \Exception
     */
    public static function getPlatformName(): string
    {
        $entityManager = self::getEntityManager();
        return $entityManager->getConnection()->getDatabasePlatform()->getName();
    }
}
