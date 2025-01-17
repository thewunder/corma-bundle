<?php
namespace Corma\CormaBundle;

use Corma\DBAL\Connection;
use Corma\DBAL\DriverManager;
use Corma\ObjectMapper;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class CormaBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()->children()
            ->arrayNode('database')->children()
            ->scalarNode('driver')->isRequired()->cannotBeEmpty()->defaultValue('pdo_mysql')->end()
            ->scalarNode('host')->isRequired()->cannotBeEmpty()->defaultValue('localhost')->end()
            ->integerNode('port')->isRequired()->defaultValue(3306)->end()
            ->scalarNode('database')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('user')->isRequired()->cannotBeEmpty()->defaultValue('root')->end()
            ->scalarNode('password')->isRequired()->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (!isset($config['database'])) {
            return;
        }

        $dbConfig = $config['database'];
        $selectDbQuery = match ($dbConfig['driver']) {
            'pdo_mysql', 'mysqli' => "USE {$dbConfig['database']}",
            'pdo_pgsql', 'pgsql' => "SET search_path TO {$dbConfig['database']}",
        };

        $container->services()->set('corma.connection', Connection::class)
            ->factory([DriverManager::class, 'getConnection'])
            ->args([$dbConfig])
            ->call('executeQuery', [$selectDbQuery]);

        $container->services()->set('corma.cache', Psr16Cache::class)
            ->args([service('cache.app')]);

        $container->services()->set('corma.orm', ObjectMapper::class)
            ->factory([ObjectMapper::class, 'withDefaults'])
            ->args([
                service('corma.connection'),
                service('service_container'),
                service('corma.cache'),
                service('event_dispatcher')
            ])->public()
            ->alias(ObjectMapper::class, 'corma.orm')
            ->public();
    }
}
