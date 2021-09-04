<?php

/**
 * @var Ep\Web\View $this 
 */

use Ep\Tests\App\Asset\MainAsset;

$this->register([
    MainAsset::class
]);

$this->registerCss('h1 {color: green}');
?>

<h1>view file</h1>