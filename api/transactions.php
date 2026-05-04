<?php
include "db.php";

header("Content-Type: application/json");

// ── Auth guard ───────────────────────────────────────────────
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = (int) $_SESSION['user']['id'];   // cast to int — never trust session data raw
$method  = $_SERVER['REQUEST_METHOD'];

// ── GET — list transactions ──────────────────────────────────
if ($method === "GET") {

    // FIX 1: use a prepared statement — never interpolate $user_id directly
    $stmt = $conn->prepare(
        "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res  = $stmt->get_result();
    $data = [];

    while ($row = $res->fetch_assoc()) {
        // FIX 2: cast amount to float so JS never receives a string → no more ₹NaN
        $row['id']     = (int)   $row['id'];
        $row['amount'] = (float) $row['amount'];
        $data[] = $row;
    }

    echo json_encode($data);
}

// ── POST — create transaction ────────────────────────────────
elseif ($method === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    // FIX 3: validate every field before touching the DB
    $type = $data['type']        ?? '';
    $desc = trim($data['description'] ?? '');
    $amt  = floatval($data['amount']  ?? 0);   // FIX 4: removed accidental double semicolon
    $cat  = trim($data['category']    ?? '');
    $date = $data['date']        ?? '';

    if (!in_array($type, ['income', 'expense'])) {
        http_response_code(422);
        echo json_encode(["error" => "type must be 'income' or 'expense'"]);
        exit;
    }
    if ($desc === '' || strlen($desc) > 255) {
        http_response_code(422);
        echo json_encode(["error" => "description is required (max 255 chars)"]);
        exit;
    }
    if ($amt <= 0) {
        http_response_code(422);
        echo json_encode(["error" => "amount must be a positive number"]);
        exit;
    }
    if ($cat === '') {
        http_response_code(422);
        echo json_encode(["error" => "category is required"]);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(422);
        echo json_encode(["error" => "date must be YYYY-MM-DD"]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO transactions (user_id, type, description, amount, category, date)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issdss", $user_id, $type, $desc, $amt, $cat, $date);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save transaction"]);
        exit;
    }

    $id = $stmt->insert_id;

    http_response_code(201);
    echo json_encode([
        "id"          => $id,
        "type"        => $type,
        "description" => $desc,
        "amount"      => $amt,    // already a float — no NaN risk
        "category"    => $cat,
        "date"        => $date,
    ]);
}

// ── DELETE — remove transaction ──────────────────────────────
elseif ($method === "DELETE") {

    // FIX 5: sanitize and validate id — never interpolate $_GET raw into a query
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Valid transaction id is required"]);
        exit;
    }

    // FIX 6: prepared statement + ownership check (user can only delete their own rows)
    $stmt = $conn->prepare(
        "DELETE FROM transactions WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Transaction not found or access denied"]);
        exit;
    }

    echo json_encode(["message" => "Deleted", "deleted_id" => $id]);
}

// ── Unsupported method ───────────────────────────────────────
else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
