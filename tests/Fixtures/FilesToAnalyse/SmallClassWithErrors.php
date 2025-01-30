<?php

declare(strict_types=1);

namespace tests\Fixtures\FilesToAnalyse;

final class SmallClassWithErrors
{
    public $untypedProperty;

    public function processArray(array $input)
    {
        foreach ($input as $key => $value) {
            if ($key == 'specific_key') {
                echo $value;
            }
        }
    }
}
