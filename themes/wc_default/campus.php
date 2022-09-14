<?php

if (!empty($_SESSION['userLogin']['user_blocking_reason'])):
    unset($_SESSION['userLogin']);
    header("Location: " . BASE);
    exit;
endif;

echo '<div class="wc_ead">';
require '_ead/index.php';
echo '</div>';
