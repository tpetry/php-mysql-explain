<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PER-CS2.0' => true,
    'declare_strict_types' => true,
    'phpdoc_align' => ['align' => 'left'],
]);
$config->setFinder($finder);

return $config;
