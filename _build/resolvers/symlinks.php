<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/DoubleCheck/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/doublecheck')) {
            $cache->deleteTree(
                $dev . 'assets/components/doublecheck/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/doublecheck/', $dev . 'assets/components/doublecheck');
        }
        if (!is_link($dev . 'core/components/doublecheck')) {
            $cache->deleteTree(
                $dev . 'core/components/doublecheck/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/doublecheck/', $dev . 'core/components/doublecheck');
        }
    }
}

return true;