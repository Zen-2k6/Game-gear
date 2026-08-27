<?php
$query = http_build_query($_GET);
header('Location: user/product.php' . ($query !== '' ? '?' . $query : ''));
exit;
