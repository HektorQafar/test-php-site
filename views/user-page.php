<?php require_once "views/header.php"; ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h1 class="card-title">User: <?= htmlspecialchars($username) ?></h1>
            <h5 class="card-subtitle text-muted mb-4">Created at: <?= htmlspecialchars($created_at) ?></h5>

            <?php if (isset($_SESSION['user']) && $id == $_SESSION['user']): ?>
                <form action="/logout" method="post">
                    <button type="submit" class="btn btn-danger">Log Out</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "views/footer.php"; ?>

