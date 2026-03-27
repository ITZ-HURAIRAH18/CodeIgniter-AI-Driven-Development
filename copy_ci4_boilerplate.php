<?php
// Script to safely copy CI4 boilerplate config to the app directory without overwriting custom ones.
$src = __DIR__ . '/vendor/codeigniter4/framework/app';
$dst = __DIR__ . '/app';

function copyDir($src, $dst) {
    if (!is_dir($dst)) @mkdir($dst, 0777, true);
    $dir = opendir($src);
    while(false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                copyDir($srcPath, $dstPath);
            } else {
                if (!file_exists($dstPath)) {
                    copy($srcPath, $dstPath);
                    echo "Copied: $dstPath\n";
                }
            }
        }
    }
    closedir($dir);
}

copyDir($src, $dst);
echo "Done copying boilerplate.\n";
