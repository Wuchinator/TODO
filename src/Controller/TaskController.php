<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/task')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $tasks = $taskRepository->findAll();
        } else {
            $tasks = $taskRepository->findBy(['user_id' => $user]);
        }

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $task = new Task();
        /** @var \App\Entity\User $user */
        $task->setUserId($user);
        $task->setStatus('Ожидает');

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Проверяем права доступа: админ видит все, остальные — только свои задачи
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/start', name: 'app_task_start', methods: ['POST'])]
    public function start(Task $task, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        if ($this->isCsrfTokenValid('start'.$task->getId(), $request->request->get('_token')) && $task->getStatus() === 'Ожидает') {
            $task->setStatus('В процессе');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index');
    }

    #[Route('/{id}/complete', name: 'app_task_complete', methods: ['POST'])]
    public function complete(Task $task, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        if ($this->isCsrfTokenValid('complete'.$task->getId(), $request->request->get('_token')) && $task->getStatus() === 'В процессе') {
            $task->setStatus('Завершено');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index');
    }

    #[Route('/{id}/cancel', name: 'app_task_cancel', methods: ['POST'])]
    public function cancel(Task $task, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $task->getUserId() !== $user) {
            throw $this->createAccessDeniedException('Доступ запрещён');
        }

        if ($this->isCsrfTokenValid('cancel'.$task->getId(), $request->request->get('_token')) && $task->getStatus() === 'В процессе') {
            $task->setStatus('Отменен');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index');
    }
}
