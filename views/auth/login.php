<div class="auth-card">
    <h2>Вхід у систему</h2>
    <p class="auth-subtitle">Будь ласка, авторизуйтесь для доступу до завдань</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login" class="auth-form">
        <div class="form-group">
            <label for="username">Логін або Email</label>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?= htmlspecialchars($oldUsername ?? 'admin') ?>" 
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Введіть будь-який пароль (тестовий режим)" 
                required
            >
        </div>

        <button type="submit" class="btn btn-primary btn-block">Увійти</button>
    </form>
</div>