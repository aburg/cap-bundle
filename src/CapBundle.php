<?php

namespace AndreasBurg\CapBundle;

use AndreasBurg\CapBundle\EventListener\KernelListener;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;


class CapBundle extends AbstractBundle
{
  public function configure(DefinitionConfigurator $definition): void
  {
    $definition->rootNode()
      ->children()
      ->scalarNode('host')->defaultValue('https://your.cap.instance.site/')->end()
      ->scalarNode('site_key')->defaultValue('1234567890')->end()
      ->scalarNode('site_secret')->defaultValue('this-is-not-your-secret')->end()
      ->scalarNode('widget_url')->defaultValue('')->end()
      ->scalarNode('wasm_url')->defaultValue('')->end()
      ->end()
    ;
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
  {
    $services = $container->services();

    $services->defaults()
      ->autowire()
      ->autoconfigure();

    $services->load('AndreasBurg\\CapBundle\\', '../src/*')
      ->exclude('../src/{DependencyInjection,Entity,CapBundle.php}');

    $host = $config['host'];
    $widgetUrl = $config['widget_url'] ?: $host.'/assets/widget.js';
    $wasmUrl = $config['wasm_url'] ?: $host.'/assets/cap_wasm_bg.wasm';

    $services->get(KernelListener::class)
      ->arg('$host', $host)
      ->arg('$siteKey', $config['site_key'])
      ->arg('$siteSecret', $config['site_secret'])
      ->arg('$widgetUrl', $widgetUrl)
      ->arg('$wasmUrl', $wasmUrl)
    ;
  }

  public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
  {
    $templateDir = \dirname(__DIR__) . '/templates';

    if ($builder->hasExtension('twig')) {
      $container->extension('twig', [
        'paths' => [
          $templateDir => 'CapBundle',
        ],
      ]);
    }

    $container->prependExtensionConfig('framework', [
      'asset_mapper' => [
        'paths' => [
          __DIR__.'/../../assets/src' => '@aburg/cap-bundle',
        ],
      ],
    ]);
  }
}
