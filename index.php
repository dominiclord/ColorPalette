<?php

require_once 'ColorPalette.php';

?>
<ul style="margin:0; padding:0;">
<?php

$color_generator = new ColorPalette('#cc44ff');

for ( $i = 0; $i < 10; $i++) {

    $color = $color_generator->render();
?>
    <li style="padding: 20px; background-color: <?php echo $color ?>"></li>
<?php

}

?>
</ul>