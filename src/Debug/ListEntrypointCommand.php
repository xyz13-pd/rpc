<?php

namespace inisire\RPC\Debug;

use inisire\RPC\Entrypoint\EntrypointRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('rpc:debug:list')]
class ListEntrypointCommand extends Command
{
    public function __construct(
        private EntrypointRegistry $entrypointRegistry
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['RPC', 'Description']);

        foreach ($this->entrypointRegistry->getEntrypoints() as $entrypoint) {
            $table->addRow([$entrypoint->getName(), $entrypoint->getDescription()]);
        }

        $table->render();

        return 0;
    }
}