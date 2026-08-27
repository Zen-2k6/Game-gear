<?php
$query = http_build_query($_GET);
header('Location: user/products.php' . ($query !== '' ? '?' . $query : ''));
exit;
