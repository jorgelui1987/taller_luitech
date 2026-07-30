<?php
$hash = password_hash('Castro16@', PASSWORD_BCRYPT);
file_put_contents('hash_output.txt', $hash);
echo "Hash generated: " . $hash;