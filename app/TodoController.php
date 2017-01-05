<?php

namespace Application;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class TodoController extends AbstractController
{
    public function listAction()
    {
        $todos = [];
        $databaseResult = mysqli_query($this->connection, 'SELECT * FROM todo');
        while($row = mysqli_fetch_assoc($databaseResult)) {
            $todos[] = $row;
        }

        return $this->render('todo/list.html.twig', [
            'todos' => $todos
        ]);
    }

    public function createAction(Request $request)
    {
        if(!$request->request->has('title')) {
            throw new \InvalidArgumentException('You should provide a title to create a todo');
        }

        $query = 'INSERT INTO todo (title) VALUES(\''.$request->request->get('title').'\');';
        mysqli_query($this->connection, $query) or die('Unable to create new task : '.mysql_error());

        return new RedirectResponse('/list');
    }

    public function closeAction($id)
    {
        $query = 'UPDATE todo SET is_done = 1 WHERE id = '.$id;
        mysqli_query($this->connection, $query) or die('Unable to update existing task : '.mysqli_error($conn));

        return new RedirectResponse('/list');
    }

    public function deleteAction($id)
    {
        $query = 'DELETE FROM todo WHERE id = '.$id;
        mysqli_query($this->connection, $query) or die('Unable to delete existing task : '.mysql_error());

        return new RedirectResponse('/list');
    }
}