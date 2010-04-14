<?php
/**
 * Upload files to a directory
 *
 * @param string $path The target directory
 *
 * @package modx
 * @subpackage processors.browser.file
 */
if (!$modx->hasPermission('file_manager')) return $modx->error->failure($modx->lexicon('permission_denied'));
$modx->lexicon->load('file');

if (empty($scriptProperties['path'])) return $modx->error->failure($modx->lexicon('file_folder_err_ns'));

/* get base paths and sanitize incoming paths */
$modx->getService('fileHandler','modFileHandler');
$root = $modx->fileHandler->getBasePath();
$directory = $modx->fileHandler->make($root.$scriptProperties['path']);

/* verify target path is a directory and writable */
if (!($directory instanceof modDirectory)) return $modx->error->failure($modx->lexicon('file_folder_err_invalid'));
if (!($directory->isReadable()) || !$directory->isWritable()) {
    return $modx->error->failure($modx->lexicon('file_folder_err_perms_upload'));
}

/* loop through each file and upload */
foreach ($_FILES as $file) {
    if ($file['error'] != 0) continue;
    if (empty($file['name'])) continue;

    $newPath = $modx->fileHandler->sanitizePath($file['name']);
    $newPath = $directory->getPath().$newPath;

    if (!@move_uploaded_file($file['tmp_name'],$newPath)) {
        return $modx->error->failure($modx->lexicon('file_err_upload'));
    }
}

/* invoke event */
$modx->invokeEvent('OnFileManagerUpload',array(
    'files' => &$_FILES,
    'directory' => &$directory,
));

return $modx->error->success();