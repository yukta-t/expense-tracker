<?php
include "db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    // Check session
    if (isset($_SESSION['user'])) {
        echo json_encode($_SESSION['user']);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Not logged in"]);
    }
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

if ($action === "register") {
    $name = $data['name'];
    $email = $data['email'];
    $pass = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
    $stmt->bind_param("sss", $name, $email, $pass);

    if ($stmt->execute()) {
        $user = ["id"=>$stmt->insert_id, "name"=>$name, "email"=>$email];
        $_SESSION['user'] = $user;
        echo json_encode($user);
    } else {
        http_response_code(400);
        echo json_encode(["error"=>"Email already exists"]);
    }
}

elseif ($action === "login") {
    $email = $data['email'];
    $pass  = $data['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($pass, $user['password'])) {
            $sessionUser = ["id"=>$user['id'], "name"=>$user['name'], "email"=>$user['email']];
            $_SESSION['user'] = $sessionUser;
            echo json_encode($sessionUser);
        } else {
            http_response_code(401);
            echo json_encode(["error"=>"Wrong password"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error"=>"User not found"]);
    }
}

elseif ($action === "logout") {
    session_destroy();
    echo json_encode(["message"=>"Logged out"]);
}
?>
