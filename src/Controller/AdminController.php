<?php
declare(strict_types=1);
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_user_index')]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_task_index');
        }

        $users = $userRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show')]
    public function show(int $id, UserRepository $userRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_task_index');
        }

        $user = $userRepository->find($id);

        if (!$user) {
            return  $this->render('admin/user/userNotFound.html.twig');
        }

        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
