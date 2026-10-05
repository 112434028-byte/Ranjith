<?php
// membership.php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: membership.html");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "fitzone";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$userId = $_SESSION["user_id"];

$fullName = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$plan = trim($_POST["plan"] ?? "");

$plans = [
    "Basic" => 999,
    "Premium" => 1999,
    "Elite" => 2999
];

if (!preg_match("/^[A-Za-z ]{2,60}$/", $fullName)) {
    die("Invalid full name.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email.");
}

if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
    die("Invalid phone number.");
}

if (!array_key_exists($plan, $plans)) {
    die("Invalid membership plan.");
}

$price = $plans[$plan];

$stmt = $conn->prepare(
    "INSERT INTO memberships
    (user_id, full_name, email, phone, plan, price)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "issssd",
    $userId,
    $fullName,
    $email,
    $phone,
    $plan,
    $price
);

if (!$stmt->execute()) {
    die("Membership could not be saved.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Confirmed</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="message-box">

    <h1>Membership Confirmed!</h1>

    <p>Thank you, <?php echo htmlspecialchars($fullName); ?>.</p>

    <p>
        Your selected plan:
        <strong><?php echo htmlspecialchars($plan); ?></strong>
    </p>

    <p>
        Price:
        <strong>₹<?php echo number_format($price, 2); ?></strong>
    </p>

    <a href="diet.html" class="btn">Get Diet Plan</a>

    <a href="home.php" class="btn">Go Home</a>

</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>