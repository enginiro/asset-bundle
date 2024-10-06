<?php
/*
 * This file is a part of the Enginiro project.
 *
 * Copyright (c) Enginiro project. For the full copyright and licensing information, please view the LICENSE.adoc file
 * that was distributed with this source code.
 */

declare(strict_types=1);

namespace Enginiro\AssetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
	private string $defaultManifestPath;

	public function __construct(string $defaultManifestPath) {
		$this->defaultManifestPath = $defaultManifestPath;
	}

	public function getConfigTreeBuilder(): TreeBuilder {
		$treeBuilder = new TreeBuilder('enginiro_asset');

		$treeBuilder->getRootNode()
			->children()
				->scalarNode('manifest_path')->defaultValue($this->defaultManifestPath)->end()
			->end();

		return $treeBuilder;
	}
}