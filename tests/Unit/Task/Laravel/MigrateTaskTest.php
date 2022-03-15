<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Laravel;

use InvalidArgumentException;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Task\Laravel\MigrateTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class MigrateTaskTest extends BaseTaskTest
{
    protected function createTask(): MigrateTask
    {
        return new MigrateTask();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Laravel('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function wrongApplicationTypeGivenThrowsException(): void
    {
        $invalidApplication = new BaseApplication('Hello world app');
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $invalidApplication, $this->deployment, []);
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesViewCacheWithoutArguments(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted("/php 'artisan' 'migrate' '--force'$/");
    }

    /**
     * @test
     */
    public function rollbackSuccessfully(): void
    {
        $this->task->rollback($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted(
            sprintf(
                "cd '%s/%s'",
                $this->application->getReleasesPath(),
                $this->deployment->getReleaseIdentifier()
            )
        );
        $this->assertCommandExecuted("/php 'artisan' 'migrate:rollback' '--force'$/");
    }
}
