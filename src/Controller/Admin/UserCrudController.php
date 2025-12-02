<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is kept for EasyAdmin compatibility but redirects to our custom AdminUserController
 * The User entity is now managed through AdminUserController at /admin/user
 */
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'User Management Dashboard');
    }

    public function index(AdminContext $context): Response
    {
        // This should not be called since we use a custom route
        // If it is called, redirect once and stop
        $response = $this->redirectToRoute('admin_user_index');
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        return $response;
    }
}
