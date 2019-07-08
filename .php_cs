<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__."/src")
    ->in(__DIR__."/tests")
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        '@Symfony' => true,
        'array_syntax' => array('syntax' => 'short'),
        'native_function_invocation' => true,
        'phpdoc_no_empty_return' => false,
    ))
    ->setFinder($finder)
;