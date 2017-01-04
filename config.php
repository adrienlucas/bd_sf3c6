<?php

if (!$conn = mysqli_connect('localhost', 'root', 'toor')) {
    die('Unable to connect to MySQL : '. mysql_errno() .' '. mysql_error());
}

mysqli_select_db($conn, 'training_todo') or die('Unable to select database "training_todo"');
