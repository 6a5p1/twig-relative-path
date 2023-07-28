<?php

namespace Twig\TokenParser;

use Twig\Node\RelativeIncludeNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

class RelativeIncludeTokenParser extends IncludeTokenParser
{
    public function parse(Token $token): Node
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        return new RelativeIncludeNode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
    public function getTag(): string
    {
        return 'include';
    }
}