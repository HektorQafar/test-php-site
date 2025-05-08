<?php require_once "views/header.php"; ?>

<div class="container mt-5">
    <h2>Hello, <?= htmlspecialchars($username) ?></h2>

    <div class="card mt-4">
        <div class="card-body">
            <h3 class="card-title">Add a Note</h3>
            <form method="POST" action="/notes">
                <div class="mb-3">
                    <textarea name="content" rows="4" cols="40" class="form-control" placeholder="Write something..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Note</button>
            </form>
        </div>
    </div>

    <div class="mt-5">
        <h3>Your Notes</h3>
        <ul class="list-group">
            <?php foreach ($notes as $note): ?>
                <li class="list-group-item">
                    <p class="mb-1"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                    <small class="text-muted"><?= htmlspecialchars($note['created_at']) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php require_once "views/footer.php"; ?>
