<?php
$query = http_build_query($_GET);
header('Location: admin/index.php' . ($query !== '' ? '?' . $query : ''));
exit;
