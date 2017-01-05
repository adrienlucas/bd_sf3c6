<?php

namespace Application;


use Application\DAO\TodoDAO;

interface TodoDAOAwareInterface
{
    public function setTodoDAO(TodoDAO $todoDAO);
}