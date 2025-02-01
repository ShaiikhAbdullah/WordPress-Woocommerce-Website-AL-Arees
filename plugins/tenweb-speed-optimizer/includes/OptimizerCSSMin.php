<?php

namespace TenWebOptimizer;

use MatthiasMullie\Minify;

/*
 * Thin wrapper around css minifiers to avoid rewriting a bunch of existing code.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerCSSMin
{
    protected $minifier = null;

    /**
     * Runs the minifier on given string of $css.
     * Returns the minified css.
     *
     * @param string $css          CSS to minify
     * @param bool   $withMinifier Process CSS in minifier or not
     *
     * @return string
     */
    public function run($css, $withMinifier = true)
    {
        $this->minifier = new Minify\CSS();

        if (!empty(trim($css))) {
            $css = $this->addDebugInfo($css);
            $css = OptimizerUtils::replace_bg($css);
            $css = OptimizerUtils::replace_font($css);
            $css = OptimizerUtils::removeBgImageMarkers($css);

            if ($withMinifier) {
                $this->minifier->add($css);

                return $this->minifier->minify();
            }
        }

        return $css;
    }

    /**
     * Static helper.
     *
     * @param string $css          CSS to minify
     * @param bool   $withMinifier
     *
     * @return string
     */
    public static function minify($css, $withMinifier = true)
    {
        $minifier = new self();

        return $minifier->run($css, $withMinifier);
    }

    /**
     * Adds a comment for just to be sure that optimizer worked on this style
     *
     * @return string
     */
    private function addDebugInfo($css)
    {
        return "\n/* 10Web Booster optimized this CSS file */\r\n" . $css;
    }
}
