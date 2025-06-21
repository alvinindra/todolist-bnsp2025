<?php
// Memulai sesi untuk menyimpan data tugas
session_start();

if (!isset($_SESSION['tasks'])) {
  $_SESSION['tasks'] = [
    ["id" => 1, "title" => "Belajar PHP Dasar", "status" => "selesai"],
    ["id" => 2, "title" => "Kerjakan Tugas Desain UX", "status" => "belum"],
    ["id" => 3, "title" => "Meeting dengan Klien", "status" => "belum"],
  ];
}

function addTask($title)
{
  $lastId = empty($_SESSION['tasks']) ? 0 : max(array_column($_SESSION['tasks'], 'id'));
  $newTask = [
    "id" => $lastId + 1,
    "title" => htmlspecialchars(strip_tags(trim($title))),
    "status" => "belum"
  ];
  $_SESSION['tasks'][] = $newTask;
}

function toggleTaskStatus($id)
{
  foreach ($_SESSION['tasks'] as &$task) {
    if ($task['id'] == $id) {
      $task['status'] = ($task['status'] == 'belum') ? 'selesai' : 'belum';
      break;
    }
  }
}

function updateTask($id, $newTitle)
{
  foreach ($_SESSION['tasks'] as &$task) {
    if ($task['id'] == $id) {
      $task['title'] = htmlspecialchars(strip_tags(trim($newTitle)));
      break;
    }
  }
}

function deleteTask($id)
{
  $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function ($task) use ($id) {
    return $task['id'] != $id;
  });
  $_SESSION['tasks'] = array_values($_SESSION['tasks']);
}

// Memproses request dari form (POST) atau link (GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    switch ($_POST['action']) {
      case 'add':
        if (!empty($_POST['task_title'])) {
          addTask($_POST['task_title']);
        }
        break;
      case 'toggle':
        if (isset($_POST['id'])) {
          toggleTaskStatus((int)$_POST['id']);
        }
        break;
      // --- CASE BARU UNTUK MENANGANI UPDATE ---
      case 'update':
        if (isset($_POST['id']) && !empty($_POST['task_title'])) {
          updateTask((int)$_POST['id'], $_POST['task_title']);
        }
        break;
    }
  }
  header("Location: index.php");
  exit;
}

// Logika GET sekarang hanya untuk delete dan edit (menampilkan form)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
  if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
    deleteTask((int)$_GET['id']);
    header("Location: index.php");
    exit;
  }
}

function showList($tasks)
{
  if (empty($tasks)) {
    echo '<p class="text-center text-gray-500 mt-4">Tidak ada tugas saat ini. Silakan tambahkan tugas baru!</p>';
    return;
  }

  $editingId = (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) ? (int)$_GET['id'] : null;

  echo '<ul class="space-y-3 mt-4">';
  usort($tasks, function ($a, $b) {
    return $a['status'] <=> $b['status'];
  });

  foreach ($tasks as $task) {
    if ($task['id'] === $editingId) {
      // --- TAMPILAN MODE EDIT ---
      echo '
            <li class="bg-slate-700 p-4 rounded-lg flex items-center justify-between gap-4">
                <form action="index.php" method="POST" class="flex-grow flex items-center gap-3">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="' . $task['id'] . '">
                    <input 
                        type="text" 
                        name="task_title" 
                        value="' . htmlspecialchars($task['title']) . '" 
                        class="flex-grow bg-slate-800 border-2 border-cyan-500 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-white"
                        autofocus
                    >
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-3 py-1.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">Simpan</button>
                        <a href="index.php" class="px-3 py-1.5 text-sm font-semibold text-white bg-gray-600 hover:bg-gray-700 rounded-md transition-colors">Batal</a>
                    </div>
                </form>
            </li>';
    } else {
      // --- TAMPILAN MODE NORMAL ---
      $taskStatusClass = $task['status'] === 'selesai' ? 'line-through text-gray-500' : 'text-gray-100';
      $isChecked = $task['status'] === 'selesai' ? 'checked' : '';

      echo '
            <li class="bg-slate-800 p-4 rounded-lg flex items-center justify-between transition-all duration-300 hover:bg-slate-700 group">
                <div class="flex items-center gap-4">
                    <form action="index.php" method="POST" class="m-0 p-0">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="' . $task['id'] . '">
                        <input 
                            type="checkbox" 
                            ' . $isChecked . '
                            onchange="this.form.submit()"
                            class="h-6 w-6 bg-slate-700 border-slate-600 rounded text-cyan-500 focus:ring-cyan-500 cursor-pointer"
                        >
                    </form>
                    <span class="font-medium ' . $taskStatusClass . '">' . $task['title'] . '</span>
                </div>
                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="?action=edit&id=' . $task['id'] . '" class="px-3 py-1 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        Edit
                    </a>
                    <a href="?action=delete&id=' . $task['id'] . '" onclick="return confirm(\'Apakah Anda yakin ingin menghapus tugas ini?\')" class="px-3 py-1 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                        Hapus
                    </a>
                </div>
            </li>';
    }
  }
  echo '</ul>';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Todolist Sederhana</title>
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
      <h1 class="text-4xl font-bold text-white">Todo List Saya</h1>
      <p class="text-slate-400 mt-2">Atur semua tugasmu di satu tempat.</p>
    </header>

    <main>
      <section id="form-tambah" class="mb-8">
        <h2 class="text-2xl font-semibold mb-3 text-cyan-400">Tambah Tugas Baru</h2>
        <form action="index.php" method="POST" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="add">
          <input type="text" name="task_title" placeholder="Contoh: Belajar Tailwind CSS" class="flex-grow bg-slate-800 border-2 border-slate-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-white" required>
          <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300">
            Tambah
          </button>
        </form>
      </section>

      <section id="daftar-tugas">
        <h2 class="text-2xl font-semibold mb-3 text-cyan-400">Daftar Tugas</h2>
        <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
          <?php
          showList($_SESSION['tasks']);
          ?>
        </div>
      </section>
    </main>
  </div>
</body>

</html>