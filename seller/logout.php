<?php
session_name('SELLER_SESSION');
session_start();
session_unset();
session_destroy();
header("Location: ../index.html");
exit();
?>
