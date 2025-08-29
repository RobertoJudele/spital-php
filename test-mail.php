<?php
var_dump(
    mail(
        'you@example.com',
        'PHP mail test',
        'Hello from PHP via msmtp',
        'From: robertojudele@gmail.com',
    ),
);
?>
