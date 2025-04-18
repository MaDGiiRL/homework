<?php

declare(strict_types=1);

require 'teory.php';

// 1) Union: [1,7] ∪ [4,12] => [1,12]
$a = new Interval(1, 7);
$b = new Interval(4, 12);
$union = $a->union($b);
echo "Union [1,7] ∪ [4,12]:\n";
foreach ($union as $i) {
    echo "  " . $i . "\n";    // => [1,12]
}

echo "\n";

// 2) Intersection: [1,7] ∩ [4,12] => [4,7]
$intersection = $a->intersection($b);
echo "Intersection [1,7] ∩ [4,12]:\n";
echo "  " . ($intersection?->__toString() ?? 'null') . "\n";  // => [4,7]

echo "\n";

// 3) Difference: [1,7] \ [4,12] => [1,3]
$difference = $a->difference($b);
echo "Difference [1,7] \\ [4,12]:\n";
foreach ($difference as $i) {
    echo "  " . $i . "\n";    // => [1,4] (vedi nota confini)
}

echo "\n";

// 4) Intersection disgiunta: [1,4] ∩ [7,12] => null
$c = new Interval(1, 4);
$d = new Interval(7, 12);
$noIntersect = $c->intersection($d);
echo "Intersection [1,4] ∩ [7,12]:\n";
echo "  " . ($noIntersect?->__toString() ?? 'null') . "\n";  // => null
