<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Memuat koneksi database
$conn = require_once __DIR__ . '/config/database.php';
function addTask($conn, $title)
{
  $safe_title = htmlspecialchars(strip_tags(trim($title)));
  $stmt = mysqli_prepare($conn, "INSERT INTO t_tugas (title, status) VALUES (?, 'belum')");
  mysqli_stmt_bind_param($stmt, "s", $safe_title);
  mysqli_stmt_execute($stmt);
}

function toggleTaskStatus($conn, $id)
{
  // 1. Dapatkan status saat ini
  $stmt_get = mysqli_prepare($conn, "SELECT status FROM t_tugas WHERE id = ?");
  mysqli_stmt_bind_param($stmt_get, "i", $id);
  mysqli_stmt_execute($stmt_get);
  $result = mysqli_stmt_get_result($stmt_get);
  $task = mysqli_fetch_assoc($result);

  if ($task) {
    // 2. Tentukan status baru
    $newStatus = ($task['status'] == 'belum') ? 'selesai' : 'belum';

    // 3. Update ke status baru
    $stmt_update = mysqli_prepare($conn, "UPDATE t_tugas SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, "si", $newStatus, $id);
    mysqli_stmt_execute($stmt_update);
  }
}

function updateTask($conn, $id, $newTitle)
{
  $safe_title = htmlspecialchars(strip_tags(trim($newTitle)));
  $stmt = mysqli_prepare($conn, "UPDATE t_tugas SET title = ? WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "si", $safe_title, $id);
  mysqli_stmt_execute($stmt);
}

function deleteTask($conn, $id)
{
  $stmt = mysqli_prepare($conn, "DELETE FROM t_tugas WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
}

function getTasks($conn)
{
  $result = mysqli_query($conn, "SELECT * FROM t_tugas ORDER BY status ASC, id DESC");
  return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$action = $_REQUEST['action'] ?? null;
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  switch ($action) {
    case 'add':
      if (!empty($_POST['task_title'])) {
        addTask($conn, $_POST['task_title']);
      }
      break;
    case 'toggle':
      if ($id > 0) {
        toggleTaskStatus($conn, $id);
      }
      break;
    case 'update':
      if ($id > 0 && !empty($_POST['task_title'])) {
        updateTask($conn, $id, $_POST['task_title']);
      }
      break;
  }
  header("Location: index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete' && $id > 0) {
  deleteTask($conn, $id);
  header("Location: index.php");
  exit;
}

// Mengambil semua tugas dari database untuk ditampilkan
$tasks = getTasks($conn);
$editingId = ($action === 'edit' && $id > 0) ? $id : null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Todolist (MySQL)</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-slate-900 text-white">
  <div class="container mx-auto max-w-2xl mt-12 px-4">
    <header class="text-center mb-8">
      <h1 class="text-4xl font-bold text-white">To-Do List BNSP 2025</h1>
      <p class="text-slate-400 mt-2">Atur semua tugasmu disini!</p>
    </header>

    <main>
      <section id="form-tambah" class="mb-8">
        <h2 class="text-2xl font-semibold mb-3 text-cyan-400">Tambah Tugas Baru</h2>
        <form action="index.php" method="POST" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="add">
          <input type="text" name="task_title" placeholder="Contoh: Makan Siang dari Rumah Makan Yurin" class="flex-grow bg-slate-800 border-2 border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-white" required>
          <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300">Tambah</button>
        </form>
      </section>

      <section id="daftar-tugas">
        <h2 class="text-2xl font-semibold mb-3 text-cyan-400">Daftar Tugas</h2>
        <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
          <?php if (empty($tasks)) : ?>
            <p class="text-center text-gray-500 mt-4">Tidak ada tugas saat ini. Silakan tambahkan tugas baru!</p>
          <?php else : ?>
            <ul class="space-y-3 mt-4">
              <?php foreach ($tasks as $task) : ?>
                <?php if ($task['id'] == $editingId) : ?>
                  <li class="bg-slate-700 p-4 rounded-lg flex items-center justify-between gap-4">
                    <form action="index.php" method="POST" class="flex-grow flex items-center gap-3">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="id" value="<?= $task['id'] ?>">
                      <input type="text" name="task_title" value="<?= htmlspecialchars($task['title']) ?>" class="flex-grow bg-slate-800 border-2 border-cyan-500 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-white" autofocus>
                      <div class="flex items-center gap-2">
                        <button type="submit" class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">Simpan</button>
                        <a href="index.php" class="px-3 py-1.5 text-sm font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-md transition-colors">Batal</a>
                      </div>
                    </form>
                  </li>
                <?php else : ?>
                  <li class="bg-slate-800 p-4 rounded-lg flex items-center justify-between transition-all duration-300 hover:bg-slate-700 group">
                    <div class="flex items-center gap-4">
                      <form action="index.php" method="POST" class="m-0 p-0">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <input type="checkbox" <?= $task['status'] === 'selesai' ? 'checked' : '' ?> onchange="this.form.submit()" class="h-6 w-6 bg-slate-700 border-slate-600 rounded text-cyan-500 focus:ring-cyan-500 cursor-pointer">
                      </form>
                      <span class="font-medium <?= $task['status'] === 'selesai' ? 'line-through text-gray-500' : 'text-gray-100' ?>"><?= htmlspecialchars($task['title']) ?></span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <a href="?action=edit&id=<?= $task['id'] ?>" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">Edit</a>
                      <a href="?action=delete&id=<?= $task['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')" class="px-3 py-1 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">Hapus</a>
                    </div>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>
</body>

</html>