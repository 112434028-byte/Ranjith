<?php
// login.php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
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

$email = trim($_POST["email"] ?? "");
$userPassword = $_POST["password"] ?? "";
$action = $_POST["action"] ?? "login";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

if (strlen($userPassword) < 6) {
    die("Password must contain at least 6 characters.");
}

/* REGISTER */
if ($action === "register") {

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("An account with this email already exists. Please login.");
    }

    $fullName = "FitZone User";
    $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)"
    );

    $stmt->bind_param("sss", $fullName, $email, $hashedPassword);

    if ($stmt->execute()) {

        $_SESSION["user_id"] = $stmt->insert_id;
        $_SESSION["user_email"] = $email;
        $_SESSION["user_name"] = $fullName;

        header("Location: home.php");
        exit();

    } else {
        die("Registration failed.");
    }
}

/* LOGIN */

$stmt = $conn->prepare(
    "SELECT id, full_name, password FROM users WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Invalid email or password.");
}

$user = $result->fetch_assoc();

if (!password_verify($userPassword, $user["password"])) {
    die("Invalid email or password.");
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_email"] = $email;
$_SESSION["user_name"] = $user["full_name"];

header("Location: home.php");
exit();

?>