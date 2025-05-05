<?php

declare(strict_types=1);

namespace UncleVersace\MaintenanceBundle\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:maintenance:enable',
    description: 'Enable maintenance mode',
)]
class MaintenanceEnableCommand extends AbstractMaintenanceCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getNewIo($input, $output);

        if ($this->isMaintenanceModeEnabled()) {
            $io->info('Maintenance mode is already enabled.');

            return $this->returnSuccess();
        }

        try {
            $this->enableMaintenanceTemplate();
            $this->disableEntryPointFile();
            $this->disableHtaccessFile();
            $this->enableMaintenanceHtaccess();
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return $this->returnFailure();
        }

        $io->success('Maintenance mode successfully enabled.');

        return $this->returnSuccess();
    }

    private function enableMaintenanceTemplate(): void
    {
        $maintenanceTemplatePath = $this->makePathAbsolute($this->maintenanceTemplatePath, $this->projectDir);

        if (!$this->filesystem->exists($maintenanceTemplatePath)) {
            throw new RuntimeException(sprintf('Maintenance template "%s" does not exists', $maintenanceTemplatePath));
        }

        $maintenanceEntryPointPath = $this->makePathAbsolute($this->maintenanceEntryPointPath, $this->projectDir);

        $this->filesystem->copy($maintenanceTemplatePath, $maintenanceEntryPointPath, true);
    }

    private function disableEntryPointFile(): void
    {
        $entryPointPath = $this->makePathAbsolute($this->entryPointPath, $this->projectDir);

        if (!$this->filesystem->exists($entryPointPath)) {
            throw new RuntimeException(sprintf('Entry point "%s" does not exists', $entryPointPath));
        }

        $tmpEntryPointPath = $this->makePathAbsolute($this->tmpEntryPointPath, $this->projectDir);

        $this->filesystem->rename($entryPointPath, $tmpEntryPointPath, true);
    }

    private function disableHtaccessFile(): void
    {
        $htaccessPath = $this->makePathAbsolute($this->htaccessPath, $this->projectDir);

        if (!$this->filesystem->exists($htaccessPath)) {
            throw new RuntimeException(sprintf('`.htaccess` "%s" does not exists', $htaccessPath));
        }

        $tmpHtaccessPath = $this->makePathAbsolute($this->tmpHtaccessPath, $this->projectDir);

        $this->filesystem->rename($htaccessPath, $tmpHtaccessPath, true);
    }

    private function enableMaintenanceHtaccess(): void
    {
        $maintenanceHtaccessPath = $this->makePathAbsolute($this->maintenanceHtaccessPath, $this->projectDir);

        if (!$this->filesystem->exists($maintenanceHtaccessPath)) {
            throw new RuntimeException(
                sprintf('Maintenance `.htaccess` "%s" does not exists', $maintenanceHtaccessPath)
            );
        }

        $htaccessPath = $this->makePathAbsolute($this->htaccessPath, $this->projectDir);

        $this->filesystem->copy($maintenanceHtaccessPath, $htaccessPath, true);
    }
}
