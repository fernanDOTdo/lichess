<?php

namespace Bundle\LichessBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class LichessExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('chess.xml');
        $loader->load('model.xml');
        $loader->load('blamer.xml');
        $loader->load('critic.xml');
        $loader->load('elo.xml');
        $loader->load('controller.xml');
        $loader->load('twig.xml');
        $loader->load('form.xml');
        $loader->load('logger.xml');
        $loader->load('cheat.xml');
        $loader->load('starter.xml');
        $loader->load('seek.xml');
        $loader->load('akismet.xml');
        $loader->load('provider.xml');
        $loader->load('listener.xml');
        $loader->load('game_config.xml');
        $loader->load('ai.xml');
        $loader->load('timeline.xml');
        $loader->load('sync.xml');
        $loader->load('renderer.xml');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->process($configuration->getConfigTree(), $configs);

        foreach (array('enabled', 'priority', 'executable_path', 'book_dir') as $option) {
            $container->setParameter('lichess.ai.crafty.'.$option, $config['ai']['crafty'][$option]);
        }
        foreach (array('enabled', 'priority') as $option) {
            $container->setParameter('lichess.ai.stupid.'.$option, $config['ai']['stupid'][$option]);
        }
        $container->setParameter('lichess.debug_assets', $config['debug_assets']);
        $container->setParameter('akismet.api_key', $config['akismet']['api_key']);
        $container->setParameter('akismet.url', $config['akismet']['url']);
        $container->setParameter('lichess.seek_matcher.use_session', $config['seek']['use_session']);
        $container->setParameter('lichess.starter.anybody.check_creator_is_active', $config['anybody_starter']['check_creator_is_active']);

        $container->setParameter('lichess.sync.path', $config['sync']['path']);
        $container->setParameter('lichess.sync.latency', $config['sync']['latency']);
        $container->setParameter('lichess.sync.delay', $config['sync']['delay']);

        if ($config['test']) {
            $loader->load('test.xml');
        }
    }
}
