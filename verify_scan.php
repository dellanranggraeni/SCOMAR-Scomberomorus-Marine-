<?php
$dir = 'C:/XAMPPS/htdocs/SCOMAR/resources/views';
$emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}]/u';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$found = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match_all($emojiPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            echo "Found in: " . $file->getPathname() . "\n";
            foreach ($matches[0] as $m) {
                echo "   Match: " . $m[0] . " (offset " . $m[1] . ")\n";
            }
            $found++;
        }
    }
}
if ($found === 0) {
    echo "CONFIRMED: ZERO EMOJIS FOUND IN ALL VIEWS!\n";
}
