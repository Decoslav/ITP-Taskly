<?php
    session_start();

    include './DB/db.php';

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $userid = $_SESSION['user_id'];

        $start_timestamp = isset($_POST['start_timestamp']) ? (int)$_POST['start_timestamp'] : null;
        $end_timestamp = isset($_POST['end_timestamp']) && !empty($_POST['end_timestamp']) ? (int)$_POST['end_timestamp'] : null;
        $task = isset($_POST['task']) ? trim($_POST['task']) : null;
        $all_day = isset($_POST['all_day']) ? (bool)$_POST['all_day'] : true;

        if($start_timestamp === null || $task === null || empty($task))
        {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            exit();
        }

        if (!$all_day && $end_timestamp !== null && $end_timestamp <= $start_timestamp) {
            echo json_encode(['success' => false, 'message' => 'End time must be after start time.']);
            exit();
        }

        $sql = "INSERT INTO termine (benutzer_id, titel, start_time, end_time, all_day) VALUES (?, ?, ?, ?, ?)";

        if($stmt = $conn->prepare($sql))
        {
            $stmt->bind_param("isiii", $_SESSION['user_id'], $task, $start_timestamp, $end_timestamp, $all_day);

            if($stmt->execute())
            {
                echo json_encode(['success' => true, 'message' => 'Task successfully saved.']); 
            } else {
                echo json_encode(['success' => false, 'message' => 'Error saving task: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
?>