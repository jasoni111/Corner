<?php
header('Access-Control-Allow-Origin: *');
if(isset($_GET["url"])){
  $url = base64_decode($_GET["url"]);
  echo "<pre>";
  echo test_input(file_get_contents($url));
  echo "</pre>";
}

function test_input($data) {
  // $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}