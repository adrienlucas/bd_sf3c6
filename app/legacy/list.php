<?php

//ini_set('display_errors', 0);

include 'config.php';
include 'header.php';

if ($_POST['action'] == 'create') {
    $query = 'INSERT INTO todo (title) VALUES(\''.$_POST['title'].'\');';
    mysqli_query($conn, $query) or die('Unable to create new task : '.mysql_error());

    header('Location: list.php');
} elseif ($_GET['action'] == 'close') {
    $query = 'UPDATE todo SET is_done = 1 WHERE id = '.intval($_GET['id']);
    mysqli_query($conn, $query) or die('Unable to update existing task : '.mysqli_error($conn));

    header('Location: list.php');
} elseif ($_GET['action'] == 'delete') {
    $query = 'DELETE FROM todo WHERE id = '.$_GET['id'];
    mysqli_query($conn, $query) or die('Unable to delete existing task : '.mysql_error());

    header('Location: list.php');
}

?>
<form action="list.php" method="post">
    <div>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" size="45"/>
        <input type="hidden" name="action" value="create"/>
        <button type="submit">send</button>
    </div>
</form>

<?php

$result = mysqli_query($conn, 'SELECT COUNT(*) FROM todo');
$count = current(mysqli_fetch_row($result));

?>
<p>
    There are <strong><?php echo $count ?></strong> tasks.
</p>

<?php $result = mysqli_query($conn, 'SELECT * FROM todo') ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
        while ($todo = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '  <td class="center">'.$todo['id'].'</td>';
            echo '  <td><a href="todo.php?id='.$todo['id'].'">'.$todo['title'].'</a></td>';
            echo '  <td class="center">';

            if ($todo['is_done']) {
                echo '<span class="done">done</span>';
            } else {
                echo '<a href="list.php?action=close&amp;id='.$todo['id'].'">close</a>';
            }

            echo '  </td>';
            echo '  <td class="center"><a href="list.php?action=delete&amp;id='.$todo['id'].'">delete</a></td>';
            echo '</tr>';
        }
     ?>
    </tbody>
</table>

<?php include 'footer.php'?>
