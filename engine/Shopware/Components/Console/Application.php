<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Console;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner as DoctrineConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Shopware\Components\DependencyInjection\ResourceLoaderAwareInterface;
use Shopware\Kernel;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Application extends BaseApplication
{
    /**
     * @var \Shopware\Kernel
     */
    private $kernel;

    /**
     * @var bool
     */
    private $commandsRegistered = false;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct('Shopware', Kernel::VERSION.' - '.'/'.$kernel->getEnvironment().($kernel->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'));
        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate process.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
    }

    /**
     * Gets the Kernel associated with this Console.
     *
     * @return KernelInterface A KernelInterface instance
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->kernel->boot();

        if (!$this->commandsRegistered) {
            $this->registerCommands();

            $this->commandsRegistered = true;
        }

        $container = $this->kernel->getResourceLoader();

        foreach ($this->all() as $command) {
            if ($command instanceof ResourceLoaderAwareInterface) {
                $command->setResourceLoader($container);
            }
        }

        if (true === $input->hasParameterOption(array('--shell', '-s'))) {
            $shell = new Shell($this);
            $shell->setProcessIsolation($input->hasParameterOption(array('--process-isolation')));
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

    protected function registerCommands()
    {
        $em = $this->kernel->getResourceLoader()->get('models');

        // setup doctrine commands
        $helperSet = $this->getHelperSet();
        $helperSet->set(new EntityManagerHelper($em), 'em');
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');

        DoctrineConsoleRunner::addCommands($this);

        $this->registerFilesystemCommands();
        $this->registerEventCommands();
    }

    protected function registerFilesystemCommands()
    {
        if (!is_dir($dir = $this->getKernel()->getDocumentRoot() . '/engine/Shopware/Commands')) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = 'Shopware\\Commands';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }
            $class = $ns.'\\'.$file->getBasename('.php');

            $r = new \ReflectionClass($class);
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract() && !$r->getConstructor()->getNumberOfRequiredParameters()) {
                $this->add($r->newInstance());
            }
        }
    }

    protected function registerEventCommands()
    {
        $this->kernel->getResourceLoader()->load('plugins');

        /** @var \Enlight_Event_EventManager $eventManager */
        $eventManager = $this->kernel->getResourceLoader()->get('events');

        $collection = new ArrayCollection();
        $collection = $eventManager->collect('Shopware_Console_Add_Command', $collection, array('subject' => $this));

        /** @var $command Command */
        foreach ($collection as $command) {
            if ($command instanceof Command) {
                $this->add($command);
            }
        }
    }
}
