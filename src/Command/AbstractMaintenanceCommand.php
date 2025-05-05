<?php

declare(strict_types=1);

namespace UncleVersace\MaintenanceBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Abstract maintenance command class
 *
 * Makes values and/or functionality available across maintenance command classes
 */
abstract class AbstractMaintenanceCommand extends Command
{
    protected string $entryPointPath = 'public/index.php';

    protected string $htaccessPath = 'public/.htaccess';

    protected string $tmpEntryPointPath = 'var/index.php';

    protected string $tmpHtaccessPath = 'var/.htaccess';

    protected string $maintenanceTemplatePath = 'templates/maintenance/maintenance.html';

    protected string $maintenanceHtaccessPath = 'templates/maintenance/.htaccess';

    protected string $maintenanceEntryPointPath = 'public/maintenance.html';

    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly string $projectDir,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function isMaintenanceModeEnabled(): bool
    {
        $entryPointPath            = $this->makePathAbsolute($this->entryPointPath, $this->projectDir);
        $maintenanceEntryPointPath = $this->makePathAbsolute($this->maintenanceEntryPointPath, $this->projectDir);

        if ($this->filesystem->exists($entryPointPath) && !$this->filesystem->exists($maintenanceEntryPointPath)) {
            return false;
        }

        return true;
    }

    protected function makePathAbsolute(string $path, string $basePath): string
    {
        return Path::makeAbsolute($path, $basePath);
    }

    protected function returnSuccess(): int
    {
        return Command::SUCCESS;
    }

    protected function returnFailure(): int
    {
        return Command::FAILURE;
    }

    protected function getNewIo(InputInterface $input, OutputInterface $output): SymfonyStyle
    {
        return new SymfonyStyle($input, $output);
    }
}
