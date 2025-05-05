<?php

declare(strict_types=1);

namespace UncleVersace\MaintenanceBundle\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:maintenance:disable',
    description: 'Disable maintenance mode',
)]
class MaintenanceDisableCommand extends AbstractMaintenanceCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getNewIo($input, $output);

        if (!$this->isMaintenanceModeEnabled()) {
            $io->info('Maintenance mode is already disabled.');

            return $this->returnSuccess();
        }

        try {
            $this->enableEntryPointFile();
            $this->disableMaintenanceTemplate();
            $this->disableMaintenanceHtaccess();
            $this->enableHtaccessFile();
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return $this->returnFailure();
        }

        $io->success('Maintenance mode successfully disabled.');

        return $this->returnSuccess();
    }

    private function enableEntryPointFile(): void
    {
        $tmpEntryPointPath = $this->makePathAbsolute($this->tmpEntryPointPath, $this->projectDir);

        if (!$this->filesystem->exists($tmpEntryPointPath)) {
            throw new RuntimeException(sprintf('Temporary entry point "%s" does not exists', $tmpEntryPointPath));
        }

        $entryPointPath = $this->makePathAbsolute($this->entryPointPath, $this->projectDir);

        $this->filesystem->rename($tmpEntryPointPath, $entryPointPath, true);
    }

    private function disableMaintenanceTemplate(): void
    {
        $maintenanceEntryPointPath = $this->makePathAbsolute($this->maintenanceEntryPointPath, $this->projectDir);

        $this->filesystem->remove($maintenanceEntryPointPath);
    }

    private function disableMaintenanceHtaccess(): void
    {
        $htaccessPath = $this->makePathAbsolute($this->htaccessPath, $this->projectDir);

        $this->filesystem->remove($htaccessPath);
    }

    private function enableHtaccessFile(): void
    {
        $tmpHtaccessPath = $this->makePathAbsolute($this->tmpHtaccessPath, $this->projectDir);

        if (!$this->filesystem->exists($tmpHtaccessPath)) {
            throw new RuntimeException(sprintf('Temporary `htaccess` "%s" does not exists', $tmpHtaccessPath));
        }

        $htaccessPath = $this->makePathAbsolute($this->htaccessPath, $this->projectDir);

        $this->filesystem->rename($tmpHtaccessPath, $htaccessPath, true);
    }
}
