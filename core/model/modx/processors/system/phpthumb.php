<?php
/**
 * Generate a thumbnail
 * 
 * @package modx
 * @subpackage processors.system
 */

/* filter path */
$src = $modx->getOption('src',$scriptProperties,'');
if (empty($src)) return '';
$src = $modx->getOption('base_path',null,MODX_BASE_PATH).$src;
if (empty($src) || !file_exists($src)) return '';

/* load phpThumb */
require_once MODX_CORE_PATH.'model/phpthumb/phpThumb.class.php';
$phpThumb = new phpThumb();

/* set cache dir */
$cachePath = $modx->getOption('core_path',null,MODX_CORE_PATH).'cache/phpthumb/';
if (!is_dir($cachePath)) $modx->cacheManager->writeTree($cachePath);
$phpThumb->config_cache_directory = $cachePath;
$phpThumb->setCacheDirectory();

/* iterate through properties */
foreach ($scriptProperties as $property => $value) {
    $phpThumb->setParameter($property,$value);
}

/* set source and generate thumbnail */
$phpThumb->setSourceFilename($src);
if (!$phpThumb->GenerateThumbnail()) return '';

$outputFilename = $modx->getOption('output_filename',$scriptProperties,false);
$captureRawData = $modx->getOption('capture_raw_data',$scriptProperties,false);
if ($outputFilename) {
    $outputFilename = ltrim($outputFilename,'/');
    $outputFilename = ltrim($outputFilename,'\\');
    if (empty($outputFilename)) return '';
    
    $outputFilename = str_replace(array(
        '{base_path}',
        '{assets_path}',
        '{core_path}',
    ),array(
        $modx->getOption('base_path',null,MODX_BASE_PATH),
        $modx->getOption('assets_path',null,MODX_ASSETS_PATH),
        $modx->getOption('core_path',null,MODX_CORE_PATH),
    ),$outputFilename);

    if ($phpThumb->RenderToFile($outputFilename)) {
        return $modx->error->success('',array('filename' => $outputFilename));
    }
    return '';
} else {
    $phpThumb->OutputThumbnail();
}

return '';