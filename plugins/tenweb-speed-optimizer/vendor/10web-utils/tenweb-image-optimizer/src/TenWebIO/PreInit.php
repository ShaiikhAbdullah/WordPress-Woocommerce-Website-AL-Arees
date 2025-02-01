<?php

namespace TenWebIO;
use TenWebWpTransients\OptimizerTransients;

class PreInit
{
    /**
     *
     * @return Init|null
     */
    public static function check($origin = 'io')
    {
        if ($origin === 'booster' && is_plugin_active('image-optimizer-wd/tenweb-image-optimizer.php')) {
            OptimizerTransients::set(TENWEBIO_PREFIX . '_origin', 'io');
            return null;
        }
        OptimizerTransients::set(TENWEBIO_PREFIX . '_origin', $origin);
        return Init::getInstance();
    }
}