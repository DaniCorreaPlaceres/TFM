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
        $connection->executeStatement('DELETE FROM expense_line');
        $connection->executeStatement('DELETE FROM expense');
        $connection->executeStatement('DELETE FROM expense_category');
        $connection->executeStatement('DELETE FROM income_attachment');
        $connection->executeStatement('DELETE FROM income_action');
        $connection->executeStatement('DELETE FROM income_payment');
        $connection->executeStatement('DELETE FROM income_line');
        $connection->executeStatement('DELETE FROM income');
        $connection->executeStatement('DELETE FROM contact');
        $connection->executeStatement('DELETE FROM closed_quarter');
        $connection->executeStatement('DELETE FROM advisory_employee_permission');
        $connection->executeStatement('DELETE FROM advisory_client_invitation');
        $connection->executeStatement('DELETE FROM advisory_client_link');
        $connection->executeStatement('DELETE FROM advisory_invitation');
        $connection->executeStatement('DELETE FROM advisory_membership');
        $connection->executeStatement('DELETE FROM advisory_firm');
        $connection->executeStatement('DELETE FROM fiscal_entity_membership');
        $connection->executeStatement('DELETE FROM fiscal_entity');
        $connection->executeStatement('DELETE FROM recovery_code');
        $connection->executeStatement('DELETE FROM password_reset');
        $connection->executeStatement('DELETE FROM email_confirmation');
        $connection->executeStatement('DELETE FROM account');
    }
}
