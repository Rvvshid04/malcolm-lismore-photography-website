<?php
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$location = $_POST["location"];
$input_event_date = $_POST["event_date"];
$event = $_POST["event"];
$message = $_POST["message_text"];

// Convert the $input_event_date to MySQL DATE format ("YYYY-MM-DD")
$event_date = date("Y-m-d", strtotime($input_event_date));

// Database connection
$conn = new mysqli('localhost', 'root', '', 'malcolmlismorephotography');
if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}

// Check if the customer with the given email already exists
$checkQuery = "SELECT cust_id FROM customers WHERE email = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // The customer with this email already exists, retrieve their cust_id
    $stmt->bind_result($cust_id);
    $stmt->fetch();
} else {
    // The customer with this email doesn't exist, insert their information
    $insertCustomerQuery = "INSERT INTO customers (email, fname, lname, phone, `location`, event_date, `event`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertCustomerQuery);
    $stmt->bind_param("sssssss", $email, $fname, $lname, $phone, $location, $event_date, $event);

    if ($stmt->execute()) {
        // Get the customer's cust_id from the last insert operation
        $cust_id = $conn->insert_id;
    } else {
        // Handle the customer insert error
        echo "Error inserting customer: " . $stmt->error;
        exit();
    }
}

// Insert the message into the "messages" table using the obtained cust_id and email
$insertMessageQuery = "INSERT INTO messages (cust_id, email, message_text) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertMessageQuery);
$stmt->bind_param("iss", $cust_id, $email, $message);

if ($stmt->execute()) {
    // Message data inserted successfully
    echo "Inquiry submitted successfully.";
} else {
    // Handle the message insert error
    echo "Error inserting message: " . $stmt->error;
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?> 




