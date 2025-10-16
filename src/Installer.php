<?php

namespace Saturio\DuckDBInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;

class Installer implements PluginInterface, EventSubscriberInterface
{
    protected IOInterface $io;
    protected Composer $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->io->write('<info>DescargadorPlugin Activado.</info>');
    }
    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall(Composer $composer, IOInterface $io) {}

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageEvent',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageEvent'
        ];
    }

    public function onPostPackageEvent(PackageEvent $event): void
    {
        $this->io->write('<comment>Downloading DuckDB C library for your OS</comment>');
        $installedPackage = $event->getOperation()->getPackage();

        if ($installedPackage->getName() !== 'satur.io/duckdb-php-full') {
            return;
        }

        $mainPackage = $this->composer->getRepositoryManager()->getLocalRepository()->findPackage('satur.io/duckdb', '*');

        $installationManager = $this->composer->getInstallationManager();
        $baseDir = $installationManager->getInstallPath($mainPackage);
        Downloader::download($baseDir . DIRECTORY_SEPARATOR . 'lib', $mainPackage->getPrettyVersion());
        $this->io->write('<info>DuckDB C lib downloaded.</info>');
    }
}
