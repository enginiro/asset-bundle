<?php
/*
 * This file is a part of the Enginiro project.
 *
 * Copyright (c) Enginiro project. For the full copyright and licensing information, please view the LICENSE.adoc file
 * that was distributed with this source code.
 */

declare(strict_types=1);

namespace Enginiro\AssetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class EnginiroAssetExtension extends ConfigurableExtension implements PrependExtensionInterface {
	private const MANIFEST_PATH = '%s/var/dat/asset-hashes.json';

	public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface {
		$defaultManifestPath = sprintf(self::MANIFEST_PATH, $container->getParameter('kernel.project_dir'));

		return new Configuration($defaultManifestPath);
	}

	protected function loadInternal(array $config, ContainerBuilder $container): void {
		$loader = new YamlFileLoader(
			$container,
			new FileLocator(__DIR__.'/../../config')
		);

		$loader->load('services.yaml');

		$definition = $container->getDefinition('enginiro.asset.version_strategy');
		$definition->replaceArgument(0, $config['manifest_path']);
	}

	public function prepend(ContainerBuilder $container): void {
		$config = [
			'assets' => [
				'version_strategy' => 'enginiro.asset.version_strategy'
			]
		];

		$container->prependExtensionConfig('framework', $config);
	}
}