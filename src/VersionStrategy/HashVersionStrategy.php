<?php
/*
 * This file is a part of the Enginiro project.
 *
 * Copyright (c) Enginiro project. For the full copyright and licensing information, please view the LICENSE.adoc file
 * that was distributed with this source code.
 */

declare(strict_types=1);

namespace Enginiro\AssetBundle\VersionStrategy;

use JsonException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use UnexpectedValueException;

/**
 * Asset version strategy.
 *
 * This version strategy reads asset hashes from a JSON manifest file and appends them as a query parameter to their
 * path. The manifest file must be a JSON map where keys are asset paths and values their hashes. Asset paths must not
 * begin with a slash.
 *
 * @package    AssetBundle
 * @subpackage VersionStrategy
 */
final class HashVersionStrategy implements VersionStrategyInterface, LoggerAwareInterface {
	/**
	 * `sprintf` format for asset path with version.
	 */
	private const PATH_FORMAT = '%s?v=%s';

	/**
	 * Manifest file path.
	 */
	private string $manifestPath;

	/**
	 * Asset hashes.
	 *
	 * @var array<string,string>|null
	 */
	private ?array $hashes = null;

	private ?LoggerInterface $logger = null;

	/**
	 * @required
	 */
	public function setLogger(LoggerInterface $logger): void {
		$this->logger = $logger;
	}

	/**
	 * HashVersionStrategy constructor.
	 *
	 * @param string $manifestPath path to manifest file
	 */
	public function __construct(string $manifestPath) {
		$this->manifestPath = $manifestPath;
	}

	/**
	 * @inheritdoc
	 */
	public function getVersion(string $path): string {
		if (strlen($path) >= 2 && $path[0] === '/') {
			$path = substr($path, 1);
		}

		return $this->getHash($path) ?? '';
	}

	/**
	 * @inheritdoc
	 */
	public function applyVersion(string $path): string {
		$version = $this->getVersion($path);

		if ($version === '') {
			return $path;
		}

		return sprintf(self::PATH_FORMAT, $path, $version);
	}

	/**
	 * Returns file hash.
	 *
	 * @param string $key file path
	 * @return string|null hash or `null` if not set
	 */
	private function getHash(string $key): ?string {
		if ($this->hashes === null) {
			$this->loadManifest();
		}

		/** @psalm-suppress PossiblyNullArgument */
		if (array_key_exists($key, $this->hashes)) {
			return strval($this->hashes[$key]);
		} else {
			if ($this->logger !== null) {
				$this->logger->debug(
					'Asset "{asset}" not found in manifest file "{manifest}".',
					['asset' => $key, 'manifest' => $this->manifestPath]
				);
			}

			return null;
		}
	}

	/**
	 * Loads file hashes from the manifest file.
	 *
	 * Produces an error log entry if the manifest file does not exist and sets the hash map to an empty array.
	 *
	 * @throws RuntimeException in case the file exists but cannot be read
	 * @throws JsonException in case the manifest is not a valid JSON file
	 * @throws UnexpectedValueException in case the manifest content has invalid structure
	 */
	private function loadManifest(): void {
		if (!file_exists($this->manifestPath)) {
			$this->hashes = [];

			if ($this->logger !== null) {
				$this->logger->error(
					'Asset manifest file "{manifest}" does not exist.',
					['manifest' => $this->manifestPath]
				);
			}

			return;
		}

		$manifest = file_get_contents($this->manifestPath);

		if ($manifest === false) {
			throw new RuntimeException(sprintf(
				'Unable to read manifest file "%s".',
				$this->manifestPath
			));
		}

		$hashes = json_decode(
			$manifest,
			true,
			512,
			JSON_THROW_ON_ERROR
		);

		if (!is_array($hashes)) {
			throw new UnexpectedValueException(sprintf(
				'Manifest file expected to contain a JSON array, %s found.',
				gettype($hashes)
			));
		}

		/** @psalm-suppress MixedPropertyTypeCoercion */
		$this->hashes = $hashes;
	}
}