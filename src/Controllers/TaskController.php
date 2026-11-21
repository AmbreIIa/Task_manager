<?php
declare(strict_types=1);

class TaskController extends BaseController
{
    private TaskStorageInterface $repository;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->repository = new TaskRepository($pdo);
    }


    public function index(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 5;
        
        $tasks = $this->repository->findAllByUser($userId, $page, $perPage);
        $totalTasks = $this->repository->countByUser($userId);
        $totalPages = (int) ceil($totalTasks/$perPage);

        $this->render('tasks/index', [
            'pageTitle'  => 'Мої завдання - TaskFlow',
            'tasks'      => $tasks,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalTasks' => $totalTasks,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('tasks/create', [
            'pageTitle' => 'Нове завдання - TaskFlow',
            'errors'    => [],
            'old'       => [],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $title = trim($_POST['title'] ?? '');
        $deadlineRaw = trim($_POST['deadline'] ?? '');
        $deadline = !empty($deadlineRaw) ? $deadlineRaw : null;

        $errors = [];

        if (mb_strlen($title) < 3) {
            $errors[] = 'Назва завдання має містити щонайменше 3 символи.';
        }

        if (!empty($errors)) {
            $this->render('tasks/create', [
                'pageTitle' => 'Нове завдання — TaskFlow',
                'errors'    => $errors,
                'old'       => ['title' => $title, 'deadline' => $deadlineRaw],
            ]);
            return;
        }

        $this->repository->add($userId, $title, $deadline);
        $this->redirect('/');
    }

    public function complete(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $taskId = (int) ($_POST['id'] ?? 0);

        if ($taskId > 0) {
            $this->repository->complete($taskId, $userId);
        }

        $this->redirect('/');
    }

    public function delete(): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user_id'];
        $taskId = (int) ($_POST['id'] ?? 0);

        if ($taskId > 0) {
            $this->repository->delete($taskId, $userId);
        }

        $this->redirect('/');
    }
}


?>