<?php
    session_start();
    include "./DB/db.php";

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $userId = $_SESSION['user_id'];
        $terminId = isset($_POST['termin_id']) ? (int)$_POST['termin_id'] : null;

        if ($terminId === null) 
        {
            echo json_encode(['success' => false, 'message' => 'Invalid task ID provided.']);
            exit();
        }

        $sql = "DELETE FROM termine WHERE termin_id = ? AND benutzer_id = ?";

        if ($stmt = $conn->prepare($sql)) 
        {
            $stmt->bind_param("ii", $terminId, $userId);

            if ($stmt->execute()) 
            {
                if ($stmt->affected_rows > 0) 
                {
                    echo json_encode(['success' => true, 'message' => 'Task successfully deleted.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No task found or unauthorized deletion.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting task: ' . $stmt->error]);
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