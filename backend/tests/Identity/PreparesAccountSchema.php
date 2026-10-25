<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

trait PreparesAccountSchema
{
    public static function setUpBeforeClass(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $output = new NullOutput();
        $create = $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--if-not-exists' => true,
        ]), $output);
        $migrate = $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), $output);
        $kernel->shutdown();
        if (0 !== $create || 0 !== $migrate) {
            throw new \RuntimeException('No se pudo preparar la base de prueba.');
        }
    }

    protected static function emptyAccounts(): void
    {
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement('DELETE FROM password_reset');
        $connection->executeStatement('DELETE FROM email_confirmation');
        $connection->executeStatement('DELETE FROM account');
    }
}
