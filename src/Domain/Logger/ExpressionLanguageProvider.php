<?php

namespace Netosoft\DomainBundle\Domain\Logger;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('json_encode', function ($arg) {
            }, function (array $variables, $value) {
                return json_encode($value);
            }),
            new ExpressionFunction('wrap_paren', function ($arg) {
            }, function (array $variables, $value) {
                return $value === null ? '' : '('.$value.')';
            }),
        ];
    }
}
