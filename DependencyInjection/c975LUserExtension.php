<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class c975LUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('c975_l_user.site', $processedConfig['site']);
        $container->setParameter('c975_l_user.registration', $processedConfig['registration']);
        $container->setParameter('c975_l_user.roleNeeded', $processedConfig['roleNeeded']);
        $container->setParameter('c975_l_user.gravatar', $processedConfig['gravatar']);
        $container->setParameter('c975_l_user.hwiOauth', $processedConfig['hwiOauth']);
        $container->setParameter('c975_l_user.databaseEmail', $processedConfig['databaseEmail']);
        $container->setParameter('c975_l_user.archiveUser', $processedConfig['archiveUser']);
        $container->setParameter('c975_l_user.publicProfile', $processedConfig['publicProfile']);
    }
}
