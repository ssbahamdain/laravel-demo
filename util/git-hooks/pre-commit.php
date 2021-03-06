#!/usr/bin/env php
<?php
/**
 * .git/hooks/pre-commit
 *
 * This pre-commit hooks will check for PHP errors (lint), and make sure the
 * code is PSR-2 compliant.
 *
 * Dependecy: PHP-CS-Fixer (https://github.com/fabpot/PHP-CS-Fixer)
 *
 * @author  Mardix  http://github.com/mardix
 * @author  Matthew Weier O'Phinney http://mwop.net/
 * @since   4 Sept 2012
 */

$exit = 0;

/*
 * collect all files which have been added, copied or
 * modified and store them in an array called output
 */
$output = array();
exec('git diff --cached --name-status --diff-filter=ACM', $output);

foreach ($output as $file) {
    if ('D' === substr($file, 0, 1)) {
        // deleted file; do nothing
        continue;
    }

    $fileName = trim(substr($file, 1));

    /*
     * Only PHP files
     */
    if (!preg_match('/\.ph(p|tml)(\.dist){0,1}$/', $fileName)) {
        continue;
    }

    /*
     * Check for parse errors
     */
    $output = array();
    $return = 0;
    exec("php -l " . escapeshellarg($fileName), $output, $return);

    if ($return != 0) {
        echo "PHP file fails to parse: " . $fileName . ":" . PHP_EOL;
        echo implode(PHP_EOL, $output) . PHP_EOL;
        $exit = 1;
        continue;
    }

    /*
     * PHP-CS-Fixer
     */
    $dryRun='';
    $output = array();
    $return = null;
    $configDry = ``;
    exec("git config --get phpcs.dry", $output, $return);
    if (!empty($output[0])) {
        $dryRun='--dry-run';
    }
    $cwd = '';
    $gitDir = $_SERVER['GIT_DIR'];
    if ($gitDir != '.git') {
        $cwd = dirname($gitDir);
    }
    if ($cwd == '') {
        $cwd = '.';
    }

    $output = array();
    $return = null;
    exec("$cwd/vendor/bin/php-cs-fixer fix -v $dryRun --level=psr2 " . escapeshellarg($fileName), $output, $return);
    $errors = array();
    for($i=0; $i<count($output); $i++) {
        if($output[$i][0]=='!') {
            continue;
        }
        $errors[]=$output[$i];
    }
    if ($return != 0 || !empty($errors)) {
        echo "PHP file contains CS issues: " . $fileName . ":" . PHP_EOL;
        echo implode(PHP_EOL, $errors) . PHP_EOL;
        $exit = 1;
        continue;
    }
}

if($exit) {
    print "Aborting commit! Run\n\tgit diff\nto see the code with fixed coding style. ".
          "Take a look at the changed file(s) and if you like the correction(s) run".
          "\n\tgit add <changed-file>\n".
          "And try to commit the changes again.\n";
}

exit($exit);
