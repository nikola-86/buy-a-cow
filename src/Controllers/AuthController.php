<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class AuthController
{
    public function __construct(
        private readonly View $view = new View()
    ) {
    }

    public function home(Request $request): void
    {
        Response::redirect('/login');
    }

    public function login(Request $request): void
    {
        $this->view->render('auth/login', ['title' => 'Login']);
    }

    public function register(Request $request): void
    {
        $this->view->render('auth/register', ['title' => 'Register']);
    }
}
