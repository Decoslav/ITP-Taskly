<?php
    session_start();
    include "./DB/db.php";

    header('Content-Type: application/json');

    $userId = $_SESSION['user_id'];

    $start_timestamp = isset($_GET['start']) ? (int)$_GET['start'] : null;
    $end_timestamp = isset($_GET['end']) ? (int)$_GET['end'] : null;

    $sql = "SELECT id as termin_id, start_time as datum_unix, end_time, titel as beschreibung, all_day FROM termine WHERE benutzer_id = ?";
    $params = [$userId];
    $types = "i";

    if ($start_timestamp !== null && $end_timestamp !== null) 
    {
        $sql .= " AND start_time BETWEEN ? AND ?";
        $params[] = $start_timestamp;
        $params[] = $end_timestamp;
        $types .= "ii";
    }

    $sql .= " ORDER BY start_time ASC";

    $tasks = [];
    if ($stmt = $conn->prepare($sql)) 
    {
        if (!empty($params)) 
        {
            $stmt->bind_param($types, ...$params);
        }
        
        if ($stmt->execute()) 
        {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) 
            {
                $row['all_day'] = (bool)$row['all_day'];
                $tasks[] = $row;
            }
            echo json_encode(['success' => true, 'tasks' => $tasks]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching tasks: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
    }

    $conn->close();
?>