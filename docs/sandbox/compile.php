<?php
require "marker.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $error="";
  $name = test_input($_POST["file_name"]);
  $file = $_FILES["file"];
  $allowed =  array('cpp');
  if($file["error"]==4){
    $error.="please upload you file";
  } 
  else if(!in_array(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION),$allowed)) $error.="please ensure syou are uploading cpp file";
  else if($file["error"]!=0) $error.="error when uploading file, code:".$file["error"];

  if($error!=""){
    exit($error);
  }
  else{
    move_uploaded_file($_FILES['file']['tmp_name'],"main.cpp");
    compile($name);
  }
}
else if($_SERVER["REQUEST_METHOD"] == "GET"){
  if(!isset($_GET["name"])){
    exit("missing name in GET params");
  }
  if(!isset($_GET["url"])){
    exit("missing url in GET params");
  }
  $url = base64_decode($_GET["url"]);
  $name = $_GET["name"];
  file_put_contents("main.cpp", fopen($url, 'r'));
  compile($name);
}
else{
  exit("please use post or get method");
}

// exec()
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// exec("g++ main.cpp",$r,$c);
// echo json_encode($r);
// echo $c;

function compile($name){
  exec("g++ main.cpp",$r,$c);
  if($c!=0)exit("{name:'$name',error:'compilation error with exit code $c'}");
  exec("a.exe",$r,$c);
  if($c!=0)exit("{name:'$name',error:'run time error with exit code $c'}");
  $mark = mark($r[0]);
  $output = [
    "name"=>$name,
    "output"=>$r,
    "mark"=>$mark
  ];
  echo json_encode($output);
  die();
}