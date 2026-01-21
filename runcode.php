<?php

/*host = "sql103.byethost14.com";
$dbname = "b14_40953560_Dbllantas";
$username = "b14_40953560";
$password = "Tempo2026";
*/




$host = "mysql-3425c3b8-franklinl-48c4.b.aivencloud.com";
$dbname = "defaultdb";
$username = "avnadmin";
$password = "AVNS_36gkMok-WoDkZ2lUyMX";

$port = '15806';




$conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
$stmt = $conn->prepare("UPDATE motorcycle_tires SET  image_url = 'https://micvfranklin.netlify.app/anuncios/llantamoto4.png'");
$stmt->execute();
echo "image_url del producto actualizado ";
?>