<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;
$activity_type = $_POST['activity_type'] ?? null;
$search_query = $_POST['search_query'] ?? null;

$conn = new mysqli("localhost", "root", "", "sundar_swadesh");

if ($book_id && $activity_type) {
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, book_id, activity_type, search_query) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $book_id, $activity_type, $search_query);
    $stmt->execute();
}
$conn->close();
