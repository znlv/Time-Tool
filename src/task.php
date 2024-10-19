<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include './database/db.php';

$user_id = $_SESSION['user_id'];
date_default_timezone_set('Europe/Amsterdam');

if (isset($_POST['start_task'])) {
    $customer_name = $_POST['customer_name'];
    $project_name = $_POST['project_name'];
    $task_description = $_POST['task_description'];
    $start_time = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, start_time, customer_name, project_name, task_description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $start_time, $customer_name, $project_name, $task_description]);
}

if (isset($_POST['end_task'])) {
    $end_time = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE user_id = ? AND end_time IS NULL ORDER BY start_time DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $task = $stmt->fetch();
    if ($task) {
        $stmt = $pdo->prepare("UPDATE tasks SET end_time = ? WHERE id = ?");
        $stmt->execute([$end_time, $task['id']]);

        $stmt = $pdo->prepare("INSERT INTO work_logs (user_id, customer_name, project_name, task_description, start_time, end_time) SELECT user_id, customer_name, project_name, task_description, start_time, ? FROM tasks WHERE id = ?");
        $stmt->execute([$end_time, $task['id']]);
    }
}
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time-Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">Time-Tool</h1>

    <div class="flex flex-col items-center mb-4 space-y-4">
        <button type="button" onclick="openStartTaskModal()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">Start Task</button>

        <form method="POST" class="w-full">
            <button type="submit" name="end_task" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400">End Task</button>
        </form>

        <h2 class="text-2xl font-semibold text-gray-700 mt-8">Your Task History</h2>
        <ul class="space-y-4">
            <?php foreach ($tasks as $task): ?>
                <li class="p-4 bg-gray-100 border border-gray-300 rounded-md cursor-pointer" onclick="openTaskModal('<?= htmlspecialchars($task['customer_name']) ?>', '<?= htmlspecialchars($task['project_name']) ?>', '<?= htmlspecialchars($task['task_description']) ?>', '<?= htmlspecialchars($task['start_time']) ?>', '<?= htmlspecialchars($task['end_time']) ?>')">
                    <span class="font-semibold">Task started at:</span> <?= htmlspecialchars($task['start_time']); ?><br>
                    <span class="font-semibold">Task ended at:</span> 
                    <?php if ($task['end_time']): ?>
                        <?= htmlspecialchars($task['end_time']); ?>
                    <?php else: ?>
                        <span class="text-red-600">In progress</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="logout.php" class="block text-center text-blue-600 font-semibold mt-6 hover:text-blue-700">Logout</a>
    </div>
</div>

<div id="startTaskModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeStartTaskModal()">&times;</span>
        <h2 class="text-xl font-bold mb-4">Start a New Task</h2>
        <form method="POST" id="startTaskForm" class="space-y-4">
            <div>
                <label for="customer_name" class="block text-gray-700">Customer Name:</label>
                <input type="text" name="customer_name" id="customer_name" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="project_name" class="block text-gray-700">Project Name:</label>
                <input type="text" name="project_name" id="project_name" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="task_description" class="block text-gray-700">Task Description:</label>
                <textarea name="task_description" id="task_description" class="w-full px-4 py-2 border border-gray-300 rounded-md" required></textarea>
            </div>
            <button type="submit" name="start_task" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400">Start Task</button>
        </form>
    </div>
</div>

<div id="taskModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTaskModal()">&times;</span>
        <h2 class="text-xl font-bold mb-4">Task Details</h2>
        <p><strong>Customer Name:</strong> <span id="modalCustomerName"></span></p>
        <p><strong>Project Name:</strong> <span id="modalProjectName"></span></p>
        <p><strong>Task Description:</strong> <span id="modalTaskDescription"></span></p>
        <p><strong>Started At:</strong> <span id="modalStartTime"></span></p>
        <p><strong>Ended At:</strong> <span id="modalEndTime"></span></p>
        <p><strong>Developed by znlv</strong></p>
    </div>
</div>

<script>
    function openStartTaskModal() {
        document.getElementById("startTaskModal").style.display = "block";
    }

    function closeStartTaskModal() {
        document.getElementById("startTaskModal").style.display = "none";
    }

    function openTaskModal(customerName, projectName, taskDescription, startTime, endTime) {
        document.getElementById("modalCustomerName").textContent = customerName;
        document.getElementById("modalProjectName").textContent = projectName;
        document.getElementById("modalTaskDescription").textContent = taskDescription;
        document.getElementById("modalStartTime").textContent = startTime;
        document.getElementById("modalEndTime").textContent = endTime || "In progress";

        document.getElementById("taskModal").style.display = "block";
    }

    function closeTaskModal() {
        document.getElementById("taskModal").style.display = "none";
    }

    window.onclick = function(event) {
        const startTaskModal = document.getElementById('startTaskModal');
        const taskModal = document.getElementById('taskModal');
        if (event.target == startTaskModal) {
            startTaskModal.style.display = "none";
        } else if (event.target == taskModal) {
            taskModal.style.display = "none";
        }
    }
</script>
</body>
</html>