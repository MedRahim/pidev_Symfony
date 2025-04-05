<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'admin_home')]
    public function index(): Response
    {
        return $this->render('BackOffice/base.html.twig');
    }



    #[Route('/admin/layouts/without-menu', name: 'layouts_without_menu')]
public function withoutMenu(): Response
{
    return $this->render('BackOffice/layouts-without-menu.html.twig');
}

#[Route('/admin/layouts/without-navbar', name: 'layouts_without_navbar')]
public function withoutNavbar(): Response
{
    return $this->render('BackOffice/layouts-without-navbar.html.twig');
}

#[Route('/admin/layouts/container', name: 'layouts_container')]
public function container(): Response
{
    return $this->render('BackOffice/layouts-container.html.twig');
}

#[Route('/admin/layouts/fluid', name: 'layouts_fluid')]
public function fluid(): Response
{
    return $this->render('BackOffice/layouts-fluid.html.twig');
}

#[Route('/admin/layouts/blank', name: 'layouts_blank')]
public function blank(): Response
{
    return $this->render('BackOffice/layouts-blank.html.twig');
}

#[Route('/admin/account', name: 'account_settings')]
public function account(): Response
{
    return $this->render('BackOffice/pages-account-settings-account.html.twig');
}

#[Route('/admin/notifications', name: 'account_notifications')]
public function notifications(): Response
{
    return $this->render('BackOffice/pages-account-settings-notifications.html.twig');
}

#[Route('/admin/connections', name: 'account_connections')]
public function connections(): Response
{
    return $this->render('BackOffice/pages-account-settings-connections.html.twig');
}

#[Route('/admin/login', name: 'auth_login')]
public function login(): Response
{
    return $this->render('BackOffice/auth-login-basic.html.twig');
}

#[Route('/admin/register', name: 'auth_register')]
public function register(): Response
{
    return $this->render('BackOffice/auth-register-basic.html.twig');
}

#[Route('/admin/forgot-password', name: 'auth_forgot_password')]
public function forgotPassword(): Response
{
    return $this->render('BackOffice/auth-forgot-password-basic.html.twig');
}

#[Route('/admin/error', name: 'misc_error')]
public function error(): Response
{
    return $this->render('BackOffice/pages-misc-error.html.twig');
}

#[Route('/admin/maintenance', name: 'misc_maintenance')]
public function maintenance(): Response
{
    return $this->render('BackOffice/pages-misc-under-maintenance.html.twig');
}


   
}
