<?php

// Expire the authentication cookie
unset($_SESSION['authenticated']);

// Expire the Administrator cookie
unset($_SESSION['isSiteAdministrator']); 

// Redirect to the login page
header('Location: /login.php');
exit();

?>