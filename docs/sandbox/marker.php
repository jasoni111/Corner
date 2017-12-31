<?php
function mark(){
  $output = ReadFile();
  $marks = 100;
  return $output;
}

function ReadFile(){
  $myfile = fopen("result.txt", "r") or die("Unable to open file!");
  return fread($myfile,filesize("result.txt"));
}