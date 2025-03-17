<?php 

function escapeStringWithLineBreaks($string) {
    $escapedString = str_replace(["\n", "\r", "\t", '"'], ["\\n", "\\r", "\\t", '\"'], $string);
    return $escapedString;
}


function processIncludes($content) {
    return preg_replace_callback('/#FINCLUDE\s+([^\s"]+)/', function ($matches) {
        $includeFile = "src/" . trim($matches[1]);
        echo "FINCLUDE $includeFile \n";
        if(file_exists($includeFile)) {
            return processStrIncludes(processIncludes(file_get_contents($includeFile)));
        }else{
            echo "ERROR CANNOT FIND FILE $includeFile";
        }
    }, $content);
}

function processStrIncludes($content) {
    return preg_replace_callback('/#FSTRINCLUDE\s+([^\s"]+)/', function ($matches) {
        $includeFile = "src/" . trim($matches[1]);
        echo "FINCLUDE $includeFile \n";
        if(file_exists($includeFile)) {
            return escapeStringWithLineBreaks(processIncludes(file_get_contents($includeFile)));
        }else{
            echo "ERROR CANNOT FIND FILE $includeFile";
        }
    }, $content);
}


$files = [
    "src/error-message.php",
    "src/error-page.php",
    "src/driver-mysql.php",
    "src/db-client.php",
    "src/ui-helper.php",
    "src/utils.php",
    "src/login-page.php",
    "src/query-page.php",
    "src/route.php"
];

$outputFile = "index.php";

$output = "";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file) . "";
        $content = processIncludes($content);
        $content = processStrIncludes($content);
        $output .= $content . "";
    } else {
        echo "Warning: File $file not found. Skipping.\n";
    }
}

file_put_contents($outputFile, $output);

echo "Merged files into $outputFile\n";
