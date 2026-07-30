<?php
$password = 'Castro16@';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;