<?php

declare(strict_types=1);

namespace tests\Fixtures\FilesToAnalyse;

final class ClassWithErrors
{
    // A property with public visibility but missing type declaration
    public $untypedProperty;

    // Inefficient array processing without proper checks
    public function processArray(array $input)
    {
        foreach ($input as $key => $value) {
            if ($key == 'specific_key') { // Weak comparison
                echo $value; // Direct echo in a loop, bad for performance
            }
        }
    }

    // Method without return type hinting
    public function calculateSomething($a, $b)
    {
        return $a + $b; // Missing type hints for parameters and return
    }

    // Method using deprecated functions
    public function useDeprecatedFunction()
    {
        $hash = md5('test'); // MD5 is insecure and outdated

        return $hash;
    }

    // Method with unused variable
    public function methodWithUnusedVariable()
    {
        $unused = 'I am not used';

        return 'Hello World';
    }

    // Method with overly long parameter list
    public function overlyComplexMethod($param1, $param2, $param3, $param4, $param5)
    {
        return compact('param1', 'param2', 'param3', 'param4', 'param5');
    }

    // Method missing error handling
    public function riskyOperation($filePath)
    {
        $file = fopen($filePath, 'r'); // No error handling for file operations
        $content = fread($file, 1024);
        fclose($file);

        return $content;
    }

    // Method with hardcoded values and magic numbers
    public function calculateDiscount($price)
    {
        $discount = $price * 0.15; // Magic number for discount percentage

        return $price - $discount;
    }
}
