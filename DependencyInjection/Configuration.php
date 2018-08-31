<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('c975_l_user');

        $rootNode
            ->children()
                ->scalarNode('site')
                    ->cannotBeEmpty()
                    ->info('Name of site to be displayed')
                ->end()
                ->booleanNode('signup')
                    ->defaultFalse()
                    ->info('If signup is allowed or not')
                ->end()
                ->scalarNode('roleNeeded')
                    ->defaultValue('ROLE_ADMIN')
                ->end()
                ->scalarNode('touUrl')
                ->end()
                ->integerNode('signinAttempts')
                    ->defaultValue(0)
                ->end()
                ->booleanNode('avatar')
                    ->defaultFalse()
                ->end()
                ->arrayNode('hwiOauth')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()
                ->booleanNode('databaseEmail')
                    ->defaultFalse()
                ->end()
                ->booleanNode('archiveUser')
                    ->defaultFalse()
                ->end()
                ->booleanNode('publicProfile')
                    ->defaultFalse()
                ->end()
                ->booleanNode('social')
                    ->defaultFalse()
                ->end()
                ->booleanNode('address')
                    ->defaultFalse()
                ->end()
                ->booleanNode('business')
                    ->defaultFalse()
                ->end()
                ->arrayNode('multilingual')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()
                ->scalarNode('entity')
                    ->defaultValue('c975L\UserBundle\Entity\User')
                ->end()
                ->scalarNode('profileForm')
                    ->defaultNull()
                ->end()
                ->scalarNode('signupForm')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
