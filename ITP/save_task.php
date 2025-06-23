<?php
    session_start();

    include './DB/db.php';

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $userid = $_SESSION['user_id'];

        $timestamp = $_POST['timestamp'];
        $task = isset($_POST['task']) ? trim($_POST['task']) : null;

        if($timestamp === null || $task  === null || empty($task))
        {
            echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
            exit();
        }

        $sql = "INSERT INTO termine (benutzer_id, titel, start_time, all_day) VALUES (?, ?, ?, ?)";

        $all_day = true;

        if($stmt = $conn->prepare($sql))
        {
            $stmt->bind_param("isii", $_SESSION['user_id'], $task, $timestamp, $all_day);

            if($stmt->execute())
            {
                echo json_encode(['success' => true, 'message' => 'Task successfully saved.']); 
            } else {
                echo json_encode(['success' => false, 'message' => 'Task no save D:']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error preparing statement']);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
?>