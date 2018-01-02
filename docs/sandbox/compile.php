<?php
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
  
}

// exec()
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function compile($name){
  if(file_exists("result.txt")){
    unlink("result.txt");
  }
  
  $compile_time = -time();
  exec("g++ main.cpp -std=c++11",$r,$c);
  if($c!=0)exit('{"name":"'.$name.'","error":"compilation error with exit code '.$c.'"}');
  $compile_time += time();

  $total_run_time = 0;
  foreach(GenList() as $k=>$v){
    $run_time = -time();
    execute($v);
    $run_time += time();
    $total_run_time += $run_time;
  }

  $output = [
    "name"=>$name,
    "result"=>LoadFile(),
    "compile_duration"=>$compile_time,
    "runtime_duration"=>$total_run_time
  ];
  echo json_encode($output);
  die();
}

function execute($arg){
  if($arg){
    exec("a.exe $arg",$r,$c);
  }
  else{
    exec("a.exe",$r,$c);
  }
  if($c!=0)exit('{"name":"'.$_GET["name"].'","error":"run time error with exit code '.$c.'"}');
  return $r;
}

function LoadFile(){
  $myfile = fopen("result.txt", "r") or die("Unable to open file!");
  return fread($myfile,filesize("result.txt"));
}

function GenList(){
  $list = [];
  for($i=1; $i<=100; $i++){
    $s = "0$i";
    while(strlen($s)<4){
      $s = "0".$s;
    }
    array_push($list,"img/$s.bmp");
  }
  return $list;
}