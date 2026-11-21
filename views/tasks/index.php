<div class="tasks-header">
    <h2>Список моїх завдань</h2>
    <a href="/tasks/create" class="btn btn-primary">+ Нове завдання</a>
</div>

<?php if (empty($tasks)): ?>
    <div class="empty-state">
        <p>У вас ще немає створених завдань.</p>
        <a href="/tasks/create">Створіть своє перше завдання прямо зараз!</a>
    </div>
<?php else: ?>
    <div class="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <div class="task-card <?= $task->isCompleted() ? 'is-done' : '' ?>">
                <div class="task-info">
                    <h3 class="task-title"><?= htmlspecialchars($task->getTitle()) ?></h3>
                    <div class="task-meta">
                        <?php if ($task->getDeadline()): ?>
                            <span class="deadline">📅 Дедлайн: <?= htmlspecialchars($task->getDeadline()) ?></span>
                        <?php endif; ?>
                        <span class="badge <?= $task->isCompleted() ? 'badge-success' : 'badge-pending' ?>">
                            <?= $task->isCompleted() ? 'Виконано' : 'В процесі' ?>
                        </span>
                    </div>
                </div>

                <div class="task-actions">
                    <form method="POST" action="/tasks/complete" style="display:inline">
                        <input type="hidden" name="id" value="<?= $task->id ?>">
                        <button type="submit" class="btn btn-icon btn-toggle" title="Змінити статус">
                            <?= $task->isCompleted() ? '↩ Відновити' : '✓ Завершити' ?>
                        </button>
                    </form>

                    <form method="POST" action="/tasks/delete" style="display:inline">
                        <input type="hidden" name="id" value="<?= $task->id ?>">
                        <button type="submit" class="btn btn-icon btn-delete" onclick="return confirm('Видалити завдання?')" title="Видалити">
                            ✕
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="/?page=<?= $page - 1 ?>" class="page-link">« Попередня</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="page-link active"><?= $i ?></span>
                <?php else: ?>
                    <a href="/?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="/?page=<?= $page + 1 ?>" class="page-link">Наступна »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>