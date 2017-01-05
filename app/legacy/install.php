<?php

if (!$conn = mysqli_connect('localhost', 'root', 'toor')) {
    die('Unable to connect to MySQL : '.mysqli_errno().' '.mysqli_error());
}

$query = 'CREATE DATABASE IF NOT EXISTS `training_todo`;';

mysqli_query($conn, $query) or die('Unable to create database : '.$query);

mysqli_select_db($conn, 'training_todo') or die('Unable to select database "training_todo": '.mysqli_error());

mysqli_query($conn, 'drop table if exists todo;') or die('Unable to delete existing table "todo": '.mysqli_error());

$query = 'CREATE TABLE IF NOT EXISTS `todo` ('."\n";
$query .= '`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,';
$query .= '`title` VARCHAR(100),'."\n";
$query .= '`is_done` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0);';

mysqli_query($conn, $query) or die('Unable to create table "todo": '.$query.' - '.mysqli_error());

$data = array(
  array('title' => 'Do the dishes', 'is_done' => 1),
  array('title' => 'Read a book', 'is_done' => 0),
  array('title' => 'Do the homework', 'is_done' => 0),
  array('title' => 'Cook some cakes for birthday', 'is_done' => 1),
);

foreach ($data as $todo) {
    mysqli_query($conn, "INSERT INTO todo (title, is_done) VALUES ('".$todo['title']."','".$todo['is_done']."');")
      or die('Unable to insert new todo : '.$todo['title']);
}

echo 'Installation done!';
echo "\n";
