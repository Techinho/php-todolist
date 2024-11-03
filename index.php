<?php
// Database connection parameters.
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todolist');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');


try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? null;

    if ($action === 'new' && $title) {
        $stmt = $conn->prepare("INSERT INTO todo (title) VALUES (:title)");
        $stmt->execute(['title' => $title]);
    } elseif ($action === 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM todo WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } elseif ($action === 'toggle' && $id) {
        $stmt = $conn->prepare("UPDATE todo SET done = 1 - done WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}

// Recuperer les taches en ordre décroissant
$stmt = $conn->prepare("SELECT * FROM todo ORDER BY created_at DESC");
$stmt->execute();
$taches = $stmt->fetchAll();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">Todo App</a>
    </nav>

    <div class="container mt-4">
        <form method="post" class="mb-4">
            <input type="text" name="title" class="form-control" placeholder="Nouvelle tâche" required>
            <button type="submit" name="action" value="new" class="btn btn-primary mt-2">Ajouter</button>
        </form>

        <ul class="list-group">
            <?php foreach ($taches as $tache): ?>
                <li class="list-group-item <?= $tache['done'] ? 'list-group-item-success' : 'list-group-item-warning' ?>">
                    <?= htmlspecialchars($tache['title']) ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $tache['id'] ?>">
                        <button type="submit" name="action" value="toggle" class="btn btn-sm btn-secondary">Toggle</button>
                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
