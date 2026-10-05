<?php
// diet.php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: diet.html");
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

$name = trim($_POST["name"] ?? "");
$age = (int)($_POST["age"] ?? 0);
$gender = trim($_POST["gender"] ?? "");
$weight = (float)($_POST["weight"] ?? 0);
$height = (float)($_POST["height"] ?? 0);
$goal = trim($_POST["goal"] ?? "");
$activity = trim($_POST["activity"] ?? "");
$dietType = trim($_POST["diet_type"] ?? "");

$validGenders = [
    "Male",
    "Female",
    "Other"
];

$validGoals = [
    "Weight Loss",
    "Muscle Gain",
    "Maintain Weight",
    "General Fitness"
];

$validActivities = [
    "Low",
    "Moderate",
    "High"
];

$validDietTypes = [
    "Vegetarian",
    "Non-Vegetarian",
    "Vegan"
];

if (!preg_match("/^[A-Za-z ]{2,60}$/", $name)) {
    die("Invalid name.");
}

if ($age < 13 || $age > 80) {
    die("Invalid age.");
}

if (!in_array($gender, $validGenders, true)) {
    die("Invalid gender.");
}

if ($weight < 30 || $weight > 300) {
    die("Invalid weight.");
}

if ($height < 100 || $height > 250) {
    die("Invalid height.");
}

if (!in_array($goal, $validGoals, true)) {
    die("Invalid fitness goal.");
}

if (!in_array($activity, $validActivities, true)) {
    die("Invalid activity level.");
}

if (!in_array($dietType, $validDietTypes, true)) {
    die("Invalid diet type.");
}

$heightMeters = $height / 100;

$bmi = $weight / ($heightMeters * $heightMeters);

$bmi = round($bmi, 1);

if ($goal === "Weight Loss") {

    $recommendation =
        "Eat vegetables, fruits, whole grains and protein-rich foods. " .
        "Control portion sizes and reduce highly processed foods.";

} elseif ($goal === "Muscle Gain") {

    $recommendation =
        "Include sufficient protein, complex carbohydrates and healthy fats. " .
        "Eat enough calories to support muscle growth.";

} elseif ($goal === "Maintain Weight") {

    $recommendation =
        "Follow a balanced diet containing protein, carbohydrates, healthy fats, " .
        "vegetables and fruits.";

} else {

    $recommendation =
        "Follow a balanced diet with vegetables, fruits, protein, whole grains " .
        "and healthy fats.";

}

if ($dietType === "Vegetarian") {

    $foods =
        "Paneer, tofu, lentils, beans, chickpeas, vegetables, fruits, " .
        "nuts, seeds and whole grains.";

} elseif ($dietType === "Vegan") {

    $foods =
        "Tofu, soy products, lentils, beans, chickpeas, vegetables, fruits, " .
        "nuts, seeds and whole grains.";

} else {

    $foods =
        "Eggs, chicken, fish, lean meat, vegetables, fruits, whole grains, " .
        "nuts and seeds.";

}

$stmt = $conn->prepare(
    "INSERT INTO diet_plans
    (user_id, name, age, gender, weight, height, goal, activity, diet_type, bmi)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isissdsssd",
    $userId,
    $name,
    $age,
    $gender,
    $weight,
    $height,
    $goal,
    $activity,
    $dietType,
    $bmi
);

if (!$stmt->execute()) {
    die("Diet plan could not be saved.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>FitZone - Your Diet Plan</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<nav class="navbar">

    <div class="logo">FITZONE</div>

    <div class="nav-links">

        <a href="home.php">Home</a>

        <a href="membership.html">Membership</a>

        <a href="diet.html">Diet</a>

        <a href="login.html">Logout</a>

    </div>

</nav>

<section class="diet-result">

    <h1>Your Diet Plan</h1>

    <div class="result-card">

        <h2>
            Hello, <?php echo htmlspecialchars($name); ?>!
        </h2>

        <p>
            <strong>Age:</strong>
            <?php echo $age; ?>
        </p>

        <p>
            <strong>Gender:</strong>
            <?php echo htmlspecialchars($gender); ?>
        </p>

        <p>
            <strong>Weight:</strong>
            <?php echo $weight; ?> kg
        </p>

        <p>
            <strong>Height:</strong>
            <?php echo $height; ?> cm
        </p>

        <p>
            <strong>BMI:</strong>
            <?php echo $bmi; ?>
        </p>

        <p>
            <strong>Goal:</strong>
            <?php echo htmlspecialchars($goal); ?>
        </p>

        <p>
            <strong>Activity:</strong>
            <?php echo htmlspecialchars($activity); ?>
        </p>

        <p>
            <strong>Diet Type:</strong>
            <?php echo htmlspecialchars($dietType); ?>
        </p>

        <h3>Recommendation</h3>

        <p>
            <?php echo htmlspecialchars($recommendation); ?>
        </p>

        <h3>Recommended Foods</h3>

        <p>
            <?php echo htmlspecialchars($foods); ?>
        </p>

        <p>
            <strong>Note:</strong>
            This is a general fitness recommendation and not medical advice.
        </p>

        <a href="home.php" class="btn">Go Home</a>

    </div>

</section>

</body>
</html>

<?php

$stmt->close();
$conn->close();

?>