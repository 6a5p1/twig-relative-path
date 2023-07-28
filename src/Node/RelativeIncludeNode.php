<?php

namespace Twig\Node;
use Twig\Compiler;
use Twig\Node\IncludeNode;

class RelativeIncludeNode extends IncludeNode
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
    protected function addGetTemplate(Compiler $compiler)
    {
        $expr = $this->getNode('expr');
        $caller_template = $this->getTemplateName();
        $template = $expr->getAttribute('value');
        $dir = dirname($caller_template);

        if (preg_match('#^\.#', $template, $m)) {
            $expr->setAttribute('value', $this->getAbsoluteFilename($dir . '/' . $template));
        }


        $compiler
            ->write('$this->loadTemplate(')
            ->subcompile($this->getNode('expr'))
            ->raw(', ')
            ->repr($this->getTemplateName())
            ->raw(', ')
            ->repr($this->getTemplateLine())
            ->raw(')');
    }
}