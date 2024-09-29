<?php declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\Argument;
use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Container;
use Dagger\Directory;
use Dagger\File;
use function Dagger\dag;

#[DaggerObject]
class PhpComposerSecurityChecker
{
    #[DaggerFunction('Returns a container with composer')]
    public function container(): Container
    {
        return dag()
            ->container()
            ->from('ghcr.io/composer/docker:latest')
            ->withoutEntrypoint();
    }

    #[DaggerFunction('Returns the composer binary')]
    public function binary(): File {
        return $this->container()
            ->file('/usr/bin/composer');
    }

    #[DaggerFunction('Verify if your PHP dependencies have known security vulnerabilities')]
    public function securityCheck(
        #[Argument('Path directory with a "composer.json" & "composer.lock" file')]
        Directory $project,
        #[Argument('Disables auditing of require-dev packages')]
        bool $noDev = false,
        #[Argument('Output format. Must be "table", "plain", "json", or "summary"')]
        string $format = 'table',
        #[Argument('Audit based on the lock file instead of the installed packages')]
        bool $locked = true,
    ): string {
        return $this->container()
            ->withMountedDirectory('/app', $project)
            ->withExec(\array_filter([
                'composer', 'audit',
                $noDev ? '--no-dev' : '',
                '--format', $format,
                $locked ? '--locked' : '',
            ]))
            ->stdout();
    }
}
