<?php

namespace Twig\TokenParser;
use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class RelativeExtendsTokenParser extends AbstractTokenParser
{
    public function getAbsoluteFilename($filename)
    {
        $path = [];
        foreach (explode('/', $filename) as $part) {
            if (empty($part) || $part === '.') continue;

            if ($part !== '..') {
                array_push($path, $part);
            } else if (count($path) > 0) {
                array_pop($path);
            } else {
                throw new \Exception('Climbing above the root is not permitted.');
            }
        }

        return join('/', $path);
    }
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        if ($this->parser->peekBlockStack()) {
            throw new SyntaxError('Cannot use "extend" in a block.', $token->getLine(), $stream->getSourceContext());
        } elseif (!$this->parser->isMainScope()) {
            throw new SyntaxError('Cannot use "extend" in a macro.', $token->getLine(), $stream->getSourceContext());
        }

        if (null !== $this->parser->getParent()) {
            throw new SyntaxError('Multiple extends tags are forbidden.', $token->getLine(), $stream->getSourceContext());
        }
        $caller_template = $stream->getSourceContext()->getName();
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $template = $expr->getAttribute('value');

        $dir = dirname($caller_template);

        if (preg_match('#^\.#', $template, $m)) {
            $expr->setAttribute('value', $this->getAbsoluteFilename($dir . '/' . $template));
        }

        $this->parser->setParent($expr);



        $stream->expect(/* Token::BLOCK_END_TYPE */3);

        return new Node();
    }

    public function getTag(): string
    {
        return 'extends';
    }
}