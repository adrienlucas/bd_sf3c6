<?php

namespace Application;


use Application\DAO\TodoDAO;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class TodoController extends AbstractController implements TodoDAOAwareInterface
{
    /**
     * @var TodoDAO
     */
    private $todoDao;

    public function setTodoDAO(TodoDAO $todoDAO)
    {
        $this->todoDao = $todoDAO;
    }

    public function listAction()
    {
        $todos = $this->todoDao->getAll();
        return $this->render('todo/list.html.twig', [
            'todos' => $todos
        ]);
    }

    public function detailAction($id)
    {
        $todo = $this->todoDao->get($id);
        if($todo === null) {
            throw new ResourceNotFoundException('Task not found...');
        }

        return $this->render('todo/todo.html.twig', [
            'todo' => $todo
        ]);
    }

    public function createAction(Request $request)
    {
        if(!$request->request->has('title')) {
            throw new \InvalidArgumentException('You should provide a title to create a todo');
        }

        $this->todoDao->create($request->request->get('title'));
        return $this->redirectToRoute('app_todo_list');
    }

    public function closeAction($id)
    {
        $this->todoDao->close($id);
        return $this->redirectToRoute('app_todo_list');
    }

    public function deleteAction($id)
    {
        $this->todoDao->delete($id);
        return $this->redirectToRoute('app_todo_list');
    }
}