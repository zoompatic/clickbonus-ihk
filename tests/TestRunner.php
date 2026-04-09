<?php
// tests/TestRunner.php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/TestCase.php';

$files = glob(__DIR__ . '/*Test.php');
$passedCount = 0;
$failedCount = 0;
$failures = [];

foreach ($files as $file) {
    require_once $file;
    $basename = basename($file, '.php');
    $className = 'Tests\\' . $basename;
    
    if (class_exists($className)) {
        echo "\nRunning tests from $className:\n";
        $testClass = new $className();
        $methods = get_class_methods($testClass);
        
        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                echo "  $method ... ";
                try {
                    $testClass->setUp();
                    $testClass->$method();
                    $testClass->tearDown();
                    echo "PASSED\n";
                    $passedCount++;
                } catch (\Exception $e) {
                    $testClass->tearDown();
                    echo "FAILED\n";
                    echo "    " . $e->getMessage() . "\n";
                    $failures[] = "$className::$method: " . $e->getMessage();
                    $failedCount++;
                }
            }
        }
    }
}

echo "\n------------------------------------------------\n";
echo "Summary: $passedCount PASSED, $failedCount FAILED\n";
if ($failedCount > 0) {
    echo "Detail Failures:\n";
    foreach ($failures as $f) {
        echo " - $f\n";
    }
    exit(1);
} else {
    echo "All tests passed successfully!\n";
    exit(0);
}
