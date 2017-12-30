<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $error="";
  $file_name = test_input($_POST["file_name"]);
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
    exec("g++ main.cpp",$r,$c);
    if($c!=0)exit("compilation error with exit code $c");
    exec("a.exe",$r,$c);
    if($c!=0)exit("run time error with exit code $c");
    echo json_encode($r);
    die();
  }
}
else{
  exit("please use post method");
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