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
class PhpSymfonySecurityChecker
{
    #[DaggerFunction('Returns a container with symfony cli')]
    public function container(): Container
    {
        return dag()
            ->container()
            ->from('ghcr.io/symfony-cli/symfony-cli:v5')
            ->withoutEntrypoint();
    }

    #[DaggerFunction('Returns the symfony cli binary')]
    public function binary(): File {
        return $this->container()
            ->file('/usr/local/bin/symfony');
    }

    #[DaggerFunction('Verify if your PHP dependencies have known security vulnerabilities')]
    public function securityCheck(
        #[Argument('The path to the composer.lock file')]
        File $lockFile,
        #[Argument('The output format (ansi, text, markdown, json, junit, or yaml)')]
        string $format = 'ansi',
        #[Argument('Whether to fail when issues are detected')]
        bool $disableExitCode = false,
    ): string {
        return $this->container()
            ->withFile('/composer.lock', $lockFile)
            ->withExec([
                'symfony', 'security:check',
                '--format', $format,
                '--dir', '/composer.lock',
                '--disable-exit-code='.($disableExitCode ? 1 : 0),
            ])
            ->stdout();
    }
}
