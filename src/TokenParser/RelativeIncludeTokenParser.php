<?php

namespace Twig\TokenParser;

use Twig\Node\RelativeIncludeNode;
use Twig\Node\IncludeNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

class RelativeIncludeTokenParser extends IncludeTokenParser
{
    public function parse(Token $token): Node
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        $str = $expr->hasAttribute('value') ? $expr->getAttribute('value') : '';

        // faster than regular expressions or other string functions
        if (strncmp($str, './', 2) === 0 || strncmp($str, '../', 3) === 0) {
            return new RelativeIncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
        }

        // fallback when value is not constant or it starts with a @Namespace or other scenarios
        return new IncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    public function getTag(): string
    {
        return 'include';
    }
}