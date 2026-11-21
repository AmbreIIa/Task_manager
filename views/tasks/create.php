<div class="form-card">
    <h2>Створення нового завдання</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/tasks/create" class="task-form">
        <div class="form-group">
            <label for="title">Назва завдання *</label>
            <input 
                type="text" 
                id="title" 
                name="title" 
                value="<?= htmlspecialchars($old['title'] ?? '') ?>" 
                placeholder="Введіть суть завдання..." 
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="deadline">Дедлайн (необов'язково)</label>
            <input 
                type="datetime-local" 
                id="deadline" 
                name="deadline" 
                value="<?= htmlspecialchars($old['deadline'] ?? '') ?>"
            >
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Зберегти завдання</button>
            <a href="/" class="btn btn-secondary">Скасувати</a>
        </div>
    </form>
</div>