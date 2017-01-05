<?php

include 'config.php';
include 'header.php';

$result = mysqli_query($conn, 'SELECT * FROM todo WHERE id = '. $_GET['id']);
$todo = mysqli_fetch_assoc($result);

?>

<p>
    <strong>Id</strong>: <?php echo $todo['id'] ?><br/>
    <strong>Title</strong>: <?php echo $todo['title'] ?><br/>
    <strong>Status</strong>: <?php echo $todo['is_done'] ? 'done' : 'not finished' ?>
</p>

<?php include 'footer.php' ?>