<?php

namespace AndreasBurg\CapBundle;

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
      ->scalarNode('host')->end()
      ->scalarNode('site_key')->end()
      ->scalarNode('site_secret')->end()
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
  }
}
