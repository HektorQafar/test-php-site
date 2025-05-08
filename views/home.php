<?php require "header.php"; ?>

<div class="container mt-5">
    <h1 class="mb-4">Home Page</h1>

    <h4>Hello, <?= htmlspecialchars($username) ?></h4>

    <form action="/" method="post" class="mt-4">
        <div class="mb-3">
            <label for="username" class="form-label">Find User</label>
            <input type="text" name="username" class="form-control" id="username" placeholder="Enter username" required>
        </div>
        <button type="submit" class="btn btn-primary">Find</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "views/footer.php"; ?>

