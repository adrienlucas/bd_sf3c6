<?php

namespace Application\DAO;

class TodoDAO
{
    private $connection;

    public function getAll()
    {
        $todos = [];

        $databaseResource = $this->query('SELECT * FROM todo');
        while($row = mysqli_fetch_assoc($databaseResource)) {
            $todos[] = $row;
        }

        return $todos;
    }

    public function get($id)
    {
        return mysqli_fetch_assoc($this->query('SELECT * FROM todo WHERE id = '.$id));
    }

    public function create($title)
    {
        if(empty($title)) {
            throw new \InvalidArgumentException('The title should not be empty.');
        }

        return $this->query('INSERT INTO todo (title) VALUES(\''.$title.'\');');
    }

    public function close($id)
    {
        return $this->query('UPDATE todo SET is_done = 1 WHERE id = '.$id);
    }

    public function delete($id)
    {
        return $this->query('DELETE FROM todo WHERE id = '.$id);
    }


    private function query($query)
    {
        $this->connection = $this->connectToDatabase();

        $result = mysqli_query($this->connection, $query);

        if($result === false) {
            throw new \RuntimeException('MySQL query is not valid.');
        }

        return $result;
    }

    private function connectToDatabase()
    {
        if (!$conn = mysqli_connect('localhost', 'root', 'toor')) {
            die('Unable to connect to MySQL : '.mysqli_errno($conn).' '.mysqli_error($conn));
        }

        mysqli_select_db($conn, 'training_todo') or die('Unable to select database "training_todo"');

        return $conn;
    }
}