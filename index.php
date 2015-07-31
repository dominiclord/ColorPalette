<?php

require_once 'ColorPalette.php';

?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ColorPalette example</title>
        <style>
            .color-list {
                margin:0;
                padding:0;
            }
            .color-block {
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <ul class="color-list">
<?php

$color_generator = new ColorPalette('#aa1100');

for ( $i = 0; $i < 10; $i++) {

    $color = $color_generator->render();

?>
            <li class="color-block" style="background-color: <?php echo $color ?>"></li>
<?php

}

?>
        </ul>
    </body>
</html>