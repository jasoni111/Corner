<?php
function mark(){
  $output = LoadFile();
  $marks = 100;
  return $output;
}

function LoadFile(){
  $myfile = fopen("result.txt", "r") or die("Unable to open file!");
  return fread($myfile,filesize("result.txt"));
}