<?php
declare(strict_types=1);

include_once __DIR__.'/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create();
$finder->in(__DIR__.'/src');

$config = new Enginiro\PhpCsFixerConfig\Config();
$config->setFinder($finder);

return $config;