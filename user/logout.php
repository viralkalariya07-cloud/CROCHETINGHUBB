<?php
session_name('USER_SESSION');
session_start();
session_unset();
session_destroy();
header("Location: ../index.html");
exit();
?>
