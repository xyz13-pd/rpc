<?php


namespace inisire\RPC\DependencyInjection;


use inisire\RPC\Bus\CommandHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RPCExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->import('*.yaml');

        $container->registerForAutoconfiguration(CommandHandlerInterface::class)
            ->addTag('rpc.command_handler');
    }
}