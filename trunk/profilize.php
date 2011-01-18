<?php

/**
 * profilize.php    Textcat profiler batch
 * 
 * Create the Ngrams based on text references.
 *
 * Please, contribute !
 *
 * @author Christophe Dri
 */

define('DIR_INPUT', 'samples/');
define('DIR_OUTPUT', 'languages/');
define('EXTENSION_IN', '.txt');
define('EXTENSION_OUT', '.lng');
define('N_GRAM_MIN_LENGTH', 1);
define('N_GRAM_MAX_LENGTH', 6);
define('N_GRAM_COUNT', 400);


/*
 *   Recursively checking for files to parse
 */

function fetchdir($path, $callback = null) {
    $excludes = array('.', '..'); // directories to exclude

    $path = rtrim($path, DIRECTORY_SEPARATOR . '/');
    $files = scandir($path);
    $files = array_diff($files, $excludes);

    foreach ($files as $file) {
        if (is_dir($path . DIRECTORY_SEPARATOR . $file))
            fetchdir($path . DIRECTORY_SEPARATOR . $file, $callback);
        // match only target extension files
        else if (!preg_match('/^.*\\' . EXTENSION_IN . '$/', $file))
            continue;
        else if (is_callable($callback, false, $call_name))
            $call_name($path . DIRECTORY_SEPARATOR . $file);
        else
            echo($path . DIRECTORY_SEPARATOR . $file . "\n");
    }
}

/*
 *    Analysing text files using the N-Gram-Based Text Categorization
 *    You might need to generate/regenerate your own language files for better accuracy / other needs
 *    cf. 	http://text-analysis.googlecode.com/files/n-gram_based_text_categorization.pdf
 */

function analyze($file) {
    $file_content = file_get_contents($file);
    $file_content = preg_replace('/[^\w\s\']+/', '', $file_content);
    preg_match_all('/[\S]+/', $file_content, $words);
    $words = $words[0];

    $tokens = array();
    foreach ($words as $word) {
        $word = '_' . strtolower($word) . '_';
        $length = strlen($word);
        for ($i = N_GRAM_MIN_LENGTH; $i <= min(N_GRAM_MAX_LENGTH, $length); $i++) {
            for ($j = 0; $j <= $length - $i; $j++) {
                $token = substr($word, $j, $i);
                if (trim($token, '_'))
                    $tokens[] = $token;
            }
        }
    }
    $tokens = array_count_values($tokens);
    arsort($tokens);
    $ngrams = array_slice(array_keys($tokens), 0, N_GRAM_COUNT);

    file_put_contents(DIR_OUTPUT . str_replace(EXTENSION_IN, EXTENSION_OUT, basename($file)), implode(PHP_EOL, $ngrams));
}

fetchdir(DIR_INPUT, 'analyze');
