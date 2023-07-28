<?php

namespace Twig\Extension;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Template;
use Twig\TemplateWrapper;
use Twig\TokenParser\RelativeExtendsTokenParser;
use Twig\TokenParser\RelativeIncludeTokenParser;
use Twig\TwigFunction;

class RelativePathExtension extends AbstractExtension
{

    public function getTokenParsers(): array
    {
        return [
            new RelativeIncludeTokenParser(),
            new RelativeExtendsTokenParser()
        ];
    }
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

    public function relative_include(Environment $env, $context, $template, $variables = [], $withContext = true, $ignoreMissing = false, $sandboxed = false)
    {
        $caller_template = null;
        foreach (debug_backtrace() as $trace) {
            if (isset($trace['object']) && $trace['object'] instanceof Template && 'Twig_Template' !== get_class($trace['object'])) {
                $caller_template = $trace['object'];
            }
        }

        // update template filename
        $dir = dirname($caller_template->getTemplateName());

        if (preg_match('#^\.#', $template, $m)) {
            $template = $this->getAbsoluteFilename($dir . '/' . $template);
        }

        $alreadySandboxed = false;
        $sandbox = null;
        if ($withContext) {
            $variables = array_merge($context, $variables);
        }

        if ($isSandboxed = $sandboxed && $env->hasExtension(SandboxExtension::class)) {
            $sandbox = $env->getExtension(SandboxExtension::class);
            if (!$alreadySandboxed = $sandbox->isSandboxed()) {
                $sandbox->enableSandbox();
            }

            foreach ((\is_array($template) ? $template : [$template]) as $name) {
                // if a Template instance is passed, it might have been instantiated outside of a sandbox, check security
                if ($name instanceof TemplateWrapper || $name instanceof Template) {
                    /** @var object */
                    $t = $name->unwrap();
                    $t->checkSecurity();
                }
            }
        }

        try {
            $loaded = null;
            try {
                $loaded = $env->resolveTemplate($template);
            } catch (LoaderError $e) {
                if (!$ignoreMissing) {
                    throw $e;
                }
            }

            return $loaded ? $loaded->render($variables) : '';
        } finally {
            if ($isSandboxed && !$alreadySandboxed) {
                $sandbox->disableSandbox();
            }
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('include', [$this, 'relative_include'], ['needs_environment' => true, 'needs_context' => true, 'is_safe' => ['all']])
        ];
    }
}
