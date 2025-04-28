<?php
require 'config.php';

// Tambah tugas
if (isset($_POST['add'])) {
    $task = trim($_POST['task']);
    if ($task !== '') {
        $stmt = $conn->prepare("INSERT INTO todos (task) VALUES (?)");
        $stmt->bind_param("s", $task);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php"); // redirect setelah tambah
    exit();
}

// Hapus tugas
if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM todos WHERE id = $id");
    header("Location: index.php"); // redirect setelah hapus
    exit();
}

// Edit tugas
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $newtask = trim($_POST['newtask']);
    if ($newtask !== '') {
        $stmt = $conn->prepare("UPDATE todos SET task = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $newtask, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php"); // redirect setelah edit
    exit();
}

// Tandai tugas selesai
if (isset($_POST['mark_done'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE todos SET is_done = 1, updated_at = CURRENT_TIMESTAMP WHERE id = $id");
    header("Location: index.php"); // redirect setelah selesai
    exit();
}

// Tandai tugas belum selesai
if (isset($_POST['mark_undone'])) {
    $id = intval($_POST['id']);
    $conn->query("UPDATE todos SET is_done = 0, updated_at = CURRENT_TIMESTAMP WHERE id = $id");
    header("Location: index.php"); // redirect setelah selesai
    exit();
}

// Ambil semua tugas
$todos = $conn->query("SELECT * FROM todos ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List PHP + MySQL</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Konfirmasi Hapus
        function confirmDelete(form) {
            if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
                form.submit();
            } else {
                return false;
            }
        }

        // Konfirmasi Edit
        function confirmEdit(form) {
            if (confirm('Apakah Anda yakin ingin menyimpan perubahan ini?')) {
                form.submit();
            } else {
                return false;
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h1>To-Do List</h1>

    <!-- Form tambah tugas -->
    <form action="" method="POST" id="todo-form">
        <input type="text" name="task" id="todo-input" placeholder="Tambah tugas baru..." required>
        <button type="submit" name="add" id="add-button">Tambah</button>
    </form>

    <!-- Daftar tugas -->
    <ul id="todo-list">
        <?php if ($todos->num_rows > 0): ?>
            <?php while ($task = $todos->fetch_assoc()): ?>
                <li class="todo-item <?= $task['is_done'] ? 'done' : '' ?>">
                    <div class="task-container">
                        <!-- Checkbox untuk status tugas -->
                        <input type="checkbox" class="todo-checkbox" 
                            <?php if ($task['is_done']) echo 'checked'; ?>
                            onchange="this.form.submit();">
                        
                        <span class="todo-text"><?= htmlspecialchars($task['task']) ?></span>
                        <span class="todo-date"><?= date('d M Y H:i:s', strtotime($task['updated_at'])) ?></span>
                    </div>

                    <div class="todo-actions">
                        <!-- Form untuk Tandai selesai atau batal -->
                        <div class="action-buttons">
                            <?php if (!$task['is_done']): ?>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" name="mark_done" class="mark-done-button">Selesai</button>
                                </form>
                            <?php else: ?>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                    <button type="submit" name="mark_undone" class="mark-undone-button">Batal</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <!-- Kolom Edit -->
                        <form action="" method="POST" class="edit-form" onsubmit="return confirmEdit(this)">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <input type="text" name="newtask" placeholder="Edit tugas..." required>
                            <button type="submit" name="edit" class="edit-button">Edit</button>
                        </form>

                        <!-- Form hapus -->
                        <form action="" method="POST" class="delete-form" onsubmit="return confirmDelete(this)">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <button type="submit" name="delete" class="delete-button">Hapus</button>
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada tugas.</p>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>
