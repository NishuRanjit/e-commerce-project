<?php

/**
 * ========================================================
 * CUSTOM BOOK INSERTION SCRIPT
 * Insert your own Nepali books into the database
 * ========================================================
 * 
 * INSTRUCTIONS:
 * 1. Fill in the $myBooks array below with YOUR book data
 * 2. Upload book covers to: uploads/ folder
 * 3. Save this file as: insert_my_books.php
 * 4. Access once: http://localhost/your-project/insert_my_books.php
 * 5. Delete this file after successful insertion
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * ========================================================
 * YOUR BOOKS DATA - EDIT THIS SECTION
 * ========================================================
 * 
 * Based on your images, I've extracted the book details.
 * Modify as needed!
 */

$myBooks = [
    // Book 1: Aama
    [
        'title' => 'Aama',
        'author' => 'Maxim Gorky',
        'genre' => 'Novel',
        'price' => 5.55,
        'description' => 'Famous novel by Maxim Gorky depicting social and political transformation. An extraordinary journey of an ordinary woman.',

        'cover_image' => 'uploads\aama.jpg',
        'file_url' => 'uploads\aama.pdf',
        'stock' => 25
    ],

    // Book 2: Abhivandya
    [
        'title' => 'Abhivandya',
        'author' => 'Shri Om Shrestha',
        'genre' => 'Essay',
        'price' => 4,
        'description' => 'Collection of essays by Shri Om Shrestha featuring deep contemplation and analysis of contemporary issues.',
        'cover_image' => 'uploads\abivandha.jpg',
        'file_url' => 'uploads\abhivandya.pdf',
        'stock' => 30
    ],

    // Book 3: Aadharbhut Nepali Byakaran
    [
        'title' => 'Aadharbhut Nepali Byakaran',
        'author' => 'Gopal Khanal',
        'genre' => 'Grammar',
        'price' => 3.5,
        'description' => 'Fundamental Nepali grammar for students of classes 6, 7, and 8. Includes simple and practical examples.',

        'cover_image' => 'uploads\adarbhut nepali byakarayan.jpg',
        'file_url' => 'uploads\nepali_byakaran.pdf',
        'stock' => 40
    ],

    // Book 4: Vaidik Sanskar ra Homa Chadhparharu
    [
        'title' => 'Vaidik Sanskar ra Hama Chadparvaharu',
        'author' => 'Govindachandra Subedi',
        'genre' => 'Culture and Festivals',
        'price' => 9.5,
        'description' => 'Comprehensive study of Nepali culture and traditions. Complete information about Vedic rituals and our festivals.',

        'cover_image' => 'uploads\baidik sanskar.jpg',
        'file_url' => 'uploads\vaidik_sanskar.pdf',
        'stock' => 15
    ],

    // Book 5: Valmiki ra Hyaas
    [
        'title' => 'Valmiki ra Hyaas',
        'author' => 'Deviprasad Subedi',
        'genre' => 'Literary Criticism',
        'price' => 12,
        'description' => 'Comparative study of great poets. Literary criticism and analysis of Valmiki and Vyasa.',

        'cover_image' => 'uploads\balmiki and byas.jpg',
        'file_url' => 'uploads\valmiki_hyaas.pdf',
        'stock' => 20
    ],

    // Book 6: Vedma Jeevan ra Jagat
    [
        'title' => 'Vedma Jeevan ra Jagat',
        'author' => 'Prof. Dr. Kul Prasad Koirala',
        'genre' => 'Spirituality',
        'price' => 4,
        'description' => 'In-depth study of philosophical and spiritual knowledge of the Vedas. Vedic perspective on life and the universe.',

        'cover_image' => 'uploads\bed ma jivan and jagat.jpg',
        'file_url' => 'uploads\vedma_jeevan.pdf',
        'stock' => 25
    ],

    // Book 7: Foundation of Translation Studies
    [
        'title' => 'Foundation of Translation Studies',
        'author' => 'Dr. Nabaraj Neupane',
        'genre' => 'Translation',
        'price' => 4,
        'description' => 'Comprehensive guide to translation studies covering paradigms and practices. Essential for translation students and professionals.',

        'cover_image' => 'uploads\Foundation of translation studies.jpg',
        'file_url' => 'uploads\Foundation of translation_studies.pdf',
        'stock' => 35
    ],

    // Book 8: Haamo Desh Darshan
    [
        'title' => 'Hamo Desh Darshan',
        'author' => 'Yogi Naraharinath',
        'genre' => 'Religion and Culture',
        'price' => 3.5,
        'description' => 'Detailed introduction to Nepal\'s cultural and religious heritage. Exploration of historical and cultural aspects of the country.',

        'cover_image' => 'uploads\Hamro desh darshan.jpg',
        'file_url' => 'uploads\Hamro desh darshan.pdf',
        'stock' => 30
    ],

    // Book 9: Hocho Thoka
    [
        'title' => 'Hocho Thoka',
        'author' => 'Shivahari Ghimire',
        'genre' => 'Mythical Essay',
        'price' => 6,
        'description' => 'Collection of Nepali myths and folktales. Modern interpretation of traditional stories.',

        'cover_image' => 'uploads\Hocho dhoka.jpg',
        'file_url' => 'uploads\hocho_thoka.pdf',
        'stock' => 20
    ],

    // Book 10: Antarashtriya Itihas
    [
        'title' => 'Antarashtriya Itihas',
        'author' => 'Dr. Balram Kayastha',
        'genre' => 'History',
        'price' => 4,
        'description' => 'Comprehensive study of world history. Analysis of international events and their impacts.',

        'cover_image' => 'uploads\international ethisas.jpg',
        'file_url' => 'uploads\antarashtriya_itihas.pdf',
        'stock' => 25
    ],

    // Book 11: Jaivic Kranti
    [
        'title' => 'Jaivic Kranti',
        'author' => 'Bharat Mansata',
        'genre' => 'Essay',
        'price' => 2.5,
        'description' => 'Detailed discussion on organic farming and sustainable development. Modern approach to agriculture sector.',

        'cover_image' => 'uploads\kranti.jpg',
        'file_url' => 'uploads\jaivic_kranti.pdf',
        'stock' => 35
    ],

    // Book 12: Kathmandu Upatyakaka Bastiharoo
    [
        'title' => 'Kathmandu Upatyakaka Bastiharu',
        'author' => 'Dr. Gopal Dhobanjar',
        'genre' => 'History and Geography',
        'price' => 6,
        'description' => 'Historical and geographical study of settlements and civilization of Kathmandu Valley.',
        'cover_image' => 'uploads\Kathmandu upatayaka bastiharu.jpg',
        'file_url' => 'uploads\ktm_basti.pdf',
        'stock' => 20
    ],

    // Book 13: Kathmandu Upatyakako Sanskritik Parampara
    [
        'title' => 'Kathmandu Upatyakako Sanskritik Parampara',
        'author' => 'Dr. Kalpana Shrestha',
        'genre' => 'Culture',
        'price' => 6,
        'description' => 'Comprehensive study of rich cultural heritage and traditions of Kathmandu Valley.',
        'cover_image' => 'uploads\kathmandu.jpg',
        'file_url' => 'uploads\ktm_sanskritik.pdf',
        'stock' => 25
    ],

    // Book 14: Laxmi Nibandha Sangraha
    [
        'title' => 'Laxmi Nibandha Sangraha',
        'author' => 'Laxmiprasad Devkota',
        'genre' => 'Essay',
        'price' => 3.5,
        'description' => 'Collection of selected essays by Mahakavi Laxmiprasad Devkota. Deep contemplation on literature and society.',

        'cover_image' => 'uploads\Laxmi nibandha sangraha.jpg',
        'file_url' => 'uploads\laxmi_nibandha.pdf',
        'stock' => 30
    ],

    // Book 15: Loktantrama Manav Adhikaar
    [
        'title' => 'Loktantrama Manav Adhikaar',
        'author' => 'Dr. Tikaram Pokharel',
        'genre' => 'Miscellaneous',
        'price' => 4.5,
        'description' => 'Detailed analysis on importance and protection of human rights in democratic systems.',

        'cover_image' => 'uploads\Loktantra ma manav adhikar.jpg',
        'file_url' => 'uploads\manav_adhikaar.pdf',
        'stock' => 28
    ],

    // Book 16: Devkotaka Mahakavya
    [
        'title' => 'Devkotaka Mahakavya',
        'author' => 'Laxmiprasad Devkota',
        'genre' => 'Epic Poetry',
        'price' => 2.22,
        'description' => 'Complete collection of epic poems by Mahakavi Devkota. Invaluable treasure of Nepali literature.',

        'cover_image' => 'uploads\Mahakabi laxmi prasad.jpg',
        'file_url' => 'uploads\devkota_mahakavya.pdf',
        'stock' => 15
    ],

    // Book 17: Vidyalaya Nepali Nibandha
    [
        'title' => 'Vidyalaya Nepali Nibandha',
        'author' => 'Rishiram Pande',
        'genre' => 'Essay',
        'price' => 3.5,
        'description' => 'Useful essay collection for school-level students. Educational and informative content.',

        'cover_image' => 'uploads\mavi nibandha.jpg',
        'file_url' => 'uploads\vidyalaya_nibandha.pdf',
        'stock' => 40
    ],

    // Book 18: Maya Thakuriko Kathaharu
    [
        'title' => 'Maya Thakuriko Kathaharu',
        'author' => 'Maya Thakuri',
        'genre' => 'Story',
        'price' => 4,
        'description' => 'Selected stories by renowned Nepali story writer Maya Thakuri.',
        'cover_image' => 'uploads\maya thakuri ka kathaharu.jpg',
        'file_url' => 'uploads\maya_kathaharu.pdf',
        'stock' => 25
    ],

    // Book 19: Mithila Lokkala
    [
        'title' => 'Mithila Lokkala',
        'author' => 'Jitbahadur Raymajhi',
        'genre' => 'Folk Art',
        'price' => 3,
        'description' => 'Detailed introduction to traditional folk art of Mithila region. Study of Madhubani paintings and other arts.',

        'cover_image' => 'uploads\Mithila chitrakala.jpg',
        'file_url' => 'uploads\mithila_lokkala.pdf',
        'stock' => 22
    ],

    // Book 20: Mithu
    [
        'title' => 'Mithu',
        'author' => 'Parasmani Dahal',
        'genre' => 'Novel',
        'price' => 4,
        'description' => 'A touching novel depicting the reality of Nepali society. Story of life\'s struggles and hopes.',

        'cover_image' => 'uploads\mithu.jpg',
        'file_url' => 'upload\mithu.pdf',
        'stock' => 30
    ],

    [
        'title' => 'Muna Madan',
        'author' => 'Laxmi Prasad Devkota',
        'genre' => 'Poetry',
        'price' => 9,
        'description' => 'An immortal masterpiece of Nepali literature. A story of love and sacrifice.',
        'cover_image' => 'uploads\Munamadan.jpg',
        'file_url' => 'uploads\muna-madan.pdf',
        'stock' => 50
    ],
    [
        'title' => 'Ek Chihan',
        'author' => 'Hridayachandra Singh Pradhan',
        'genre' => 'Novel',
        'price' => 3,
        'description' => 'An important novel in Nepali literature.',
        'cover_image' => 'uploads\naso.jpg',
        'file_url' => 'uploads\ek-chihan.pdf',
        'stock' => 25
    ],
    [
        'title' => 'Nepalko Saral Itihas',
        'author' => 'Dr. Bam B. Adhikari',
        'genre' => 'History',
        'price' => 9.5,
        'description' => 'A simple and comprehensive description of Nepal\'s history.',
        'cover_image' => 'uploads\nepak ko sarar ethisas.jpg',
        'file_url' => 'uploads\nepalko-saral-itihas.pdf',
        'stock' => 40
    ],
    [
        'title' => 'Nepalma Jatra',
        'author' => 'Tejaprakash Shrestha',
        'genre' => 'Culture',
        'price' => 4,
        'description' => 'Detailed description of various festivals and celebrations in Nepal.',
        'cover_image' => 'uploads\Nepal ma jatra.jpg',
        'file_url' => 'uploads\nepalma-jatra.pdf',
        'stock' => 35
    ],
    [
        'title' => 'Nepali Ukhan Tukka Ra Gaikhane Katha',
        'author' => 'Rishiram Pande',
        'genre' => 'Folklore',
        'price' => 2.5,
        'description' => 'Collection of Nepali folk literature. Proverbs, riddles and stories.',
        'cover_image' => 'uploads\Nepali ukhan.jpg',
        'file_url' => 'uploads\nepali-ukhan-tukka.pdf',
        'stock' => 45
    ],
    [
        'title' => 'Prachin Madhyakalin Nepalko Itihas',
        'author' => 'Dr. Balaram Kayastha',
        'genre' => 'History',
        'price' => 5.5,
        'description' => 'Detailed historical study of ancient and medieval Nepal.',
        'cover_image' => 'uploads\Prachin tatha madhaykalin ethisas.jpg',
        'file_url' => 'uploads\prachin-madhyakalin-itihas.pdf',
        'stock' => 30
    ],
    [
        'title' => 'Prarambhik Nepali Nibandha',
        'author' => 'Rishiram Pande',
        'genre' => 'Essay',
        'price' => 2.5,
        'description' => 'Collection of Nepali essays for primary school level.',
        'cover_image' => 'uploads\pravi nibandha.jpg',
        'file_url' => 'uploads\prarambhik-nepali-nibandh.pdf',
        'stock' => 60
    ],
    [
        'title' => 'Ramayan',
        'author' => 'Bhanubhakta Acharya',
        'genre' => 'Poetry',
        'price' => 7,
        'description' => 'Epic Ramayana translated into Nepali language.',
        'cover_image' => 'uploads\Ramayan.jpg',
        'file_url' => 'uploads\ramayan.pdf',
        'stock' => 55
    ],
    [
        'title' => 'Science Stories',
        'author' => 'Sumit Pokhrel',
        'genre' => 'Science',
        'price' => 3,
        'description' => 'A collection of stories for School Level. Educational science stories in English.',
        'cover_image' => 'uploads\scienct stories.jpg',
        'file_url' => 'uploads\science-stories.pdf',
        'stock' => 40
    ],
    [
        'title' => 'Setobagh',
        'author' => 'Diamond Shumsher Rana',
        'genre' => 'Novel',
        'price' => 6.5,
        'description' => 'Popular historical novel in Nepali literature.',
        'cover_image' => 'uploads\seto bagh.jpg',
        'file_url' => 'uploads\setobagh.pdf',
        'stock' => 35
    ],
    [
        'title' => 'Sirjanako Aatho',
        'author' => 'Prof. Dr. Kul Prasad Koirala',
        'genre' => 'Literary Collection',
        'price' => 6,
        'description' => 'Collection of literary writings.',
        'cover_image' => 'uploads\sisna ko aatho.jpg',
        'file_url' => 'uploads\sisna ko aatho.pdf',
        'stock' => 25
    ],
    [
        'title' => 'Adharbhut Nepali Nibandha',
        'author' => 'Rishiram Pande',
        'genre' => 'Essay',
        'price' => 3,
        'description' => 'Basic Nepali essays for school level.',
        'cover_image' => 'uploads\aadarbhut nibandha.jpg',
        'file_url' => 'uploads\adharbhut-nepali-nibandh.pdf',
        'stock' => 50
    ],
    [
        'title' => 'Anshuvarma',
        'author' => 'Dr. Balaram Kayastha',
        'genre' => 'History',
        'price' => 2.5,
        'description' => 'History of Nepal during the reign of Shri Samantadeva Maharajadhiraja.',
        'cover_image' => 'uploads\anshu barma.jpg',
        'file_url' => 'uploads\anshuvarma.pdf',
        'stock' => 30
    ],
    [
        'title' => 'Bargiya Premko Chamatkar',
        'author' => 'Bhagendraraj Vaidya',
        'genre' => 'Novel',
        'price' => 4,
        'description' => 'Love story based on social reality.',
        'cover_image' => 'uploads\Bargiya prem.jpg',
        'file_url' => 'uploads\bargiya-premko-chamatkar.pdf',
        'stock' => 28
    ],
    [
        'title' => 'Bhaktapurka Vaiparva-Jatrotsav',
        'author' => 'Dr. Balaram Kayastha',
        'genre' => 'History/Culture',
        'price' => 3.75,
        'description' => 'Detailed study of various festivals and celebrations of Bhaktapur.',
        'cover_image' => 'uploads\bhaktapur ka jatra utsav.jpg',
        'file_url' => 'uploads\bhaktapurka-vaiparva.pdf',
        'stock' => 32
    ],
    [
        'title' => 'Bhaktapurka Aitihasik Tatha Sanskritik Sampadaharu',
        'author' => 'Dr. Balaram Kayastha',
        'genre' => 'History/Culture',
        'price' => 3,
        'description' => 'Study of historical and cultural heritage of Bhaktapur.',
        'cover_image' => 'uploads\bhaktapur ka sanskrit sampadaharu.jpg',
        'file_url' => 'uploads\bhaktapurka-sampadaharu.pdf',
        'stock' => 35
    ],
    [
        'title' => 'Bharatko Adhunik Itihas',
        'author' => 'Dr. Balaram Kayastha',
        'genre' => 'History',
        'price' => 4,
        'description' => 'Detailed history of modern India.',
        'cover_image' => 'uploads\Bharat ko adhunik ethisas.jpg',
        'file_url' => 'uploads\bharatko-adhunik-itihas.pdf',
        'stock' => 38
    ],
    [
        'title' => 'Bisarjan',
        'author' => 'Lokendra B. Chand',
        'genre' => 'Story',
        'price' => 3,
        'description' => 'Popular and much-discussed story collection.',
        'cover_image' => 'uploads\birsshan.jpg',
        'file_url' => 'uploads\bisarjan.pdf',
        'stock' => 42
    ],
    [
        'title' => 'Vishwayuddha Dekhi Sheetyuddha Samma',
        'author' => 'Bam B. Adhikari',
        'genre' => 'History',
        'price' => 6.5,
        'description' => 'Detailed historical account from World War to Cold War.',
        'cover_image' => 'uploads\bishwa udhya dekhi situdhya samma.jpg',
        'file_url' => 'uploads\vishwayuddha-sheetyuddha.pdf',
        'stock' => 28
    ],
    [
        'title' => 'Jansangharshka Tees Barsha',
        'author' => 'Prof. Dr. Badrinarayan Gautam',
        'genre' => 'Political',
        'price' => 4.5,
        'description' => 'Thirty years history of people\'s movement in Nepal.',
        'cover_image' => 'uploads\janasangrashka tis barsha.jpg',
        'file_url' => 'uploads\jansangarshka-tees-barsha.pdf',
        'stock' => 30
    ],

    [
        'title' => 'Madhyamik Nepali Byakaran',
        'author' => 'Gopal Khanal',
        'genre' => 'Grammar',
        'price' => 5,
        'description' => 'Comprehensive Nepali grammar book for secondary level. Covers principles and practice.',
        'cover_image' => 'uploads\madhyamik byakaryan.jpg',
        'file_url' => 'uploads\madhyamik-nepali-byakaran.pdf',
        'stock' => 45
    ],
    [
        'title' => 'Essays Primary',
        'author' => 'Rishiram Pande',
        'genre' => 'Essay',
        'price' => 2.5,
        'description' => 'Collection of essays for primary level students.',
        'cover_image' => 'uploads\Primary essays.jpg',
        'file_url' => 'uploads\essays-primary.pdf',
        'stock' => 55
    ],
    [
        'title' => 'The Wake of the White Tiger',
        'author' => 'Diamond Shumsher Rana',
        'genre' => 'Novel',
        'price' => 7,
        'description' => 'Epic historical novel by Diamond Shumsher Rana.',
        'cover_image' => 'uploads\the wake of the white tiger.jpg',
        'file_url' => 'uploads\wake-of-white-tiger.pdf',
        'stock' => 30
    ],
    [
        'title' => 'Udhyeko Aakash',
        'author' => 'Bishnu Bhandari Acharya',
        'genre' => 'Story',
        'price' => 3.5,
        'description' => 'Collection of inspiring stories.',
        'cover_image' => 'uploads\ugrayako aakash.jpg',
        'file_url' => 'uploads\udhyeko-aakash.pdf',
        'stock' => 40
    ]


];

/**
 * ========================================================
 * HTML INTERFACE & INSERTION LOGIC
 * ========================================================
 */

$message = '';
$messageType = '';
$inserted = 0;
$errors = 0;
$skipped = 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'replace') {
        // Clear existing data (CAREFUL!)
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->query("DELETE FROM reviews");
        $conn->query("DELETE FROM order_items");
        $conn->query("DELETE FROM cart");
        $conn->query("DELETE FROM books");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $message .= " Cleared existing books<br>";
    }

    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO books (title, author, description, price, genre, cover_image, file_url, stock) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($myBooks as $book) {
        // Check if book already exists
        if ($action === 'add') {
            $check = $conn->prepare("SELECT book_id FROM books WHERE title = ? AND author = ?");
            $check->bind_param("ss", $book['title'], $book['author']);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $skipped++;
                $check->close();
                continue;
            }
            $check->close();
        }

        $stmt->bind_param(
            "sssdsssi",
            $book['title'],
            $book['author'],
            $book['description'],
            $book['price'],
            $book['genre'],
            $book['cover_image'],
            $book['file_url'],
            $book['stock']
        );

        if ($stmt->execute()) {
            $inserted++;
        } else {
            $errors++;
        }
    }

    $stmt->close();

    $message .= " <strong>Successfully inserted: {$inserted} books</strong><br>";
    if ($skipped > 0) {
        $message .= "⏭ Skipped (already exists): {$skipped} books<br>";
    }
    if ($errors > 0) {
        $message .= "Errors: {$errors} books<br>";
    }

    $messageType = 'success';
}

// Check existing books
$check = $conn->query("SELECT COUNT(*) as count FROM books");
$existing = $check->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert My Nepali Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 900px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .book-preview {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .book-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .book-item h6 {
            color: #667eea;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .book-item small {
            color: #666;
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 20px;
            margin: 20px 0;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2> Insert Your Nepali Books</h2>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($myBooks); ?></div>
                <div class="stat-label">Books Ready</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $existing; ?></div>
                <div class="stat-label">Currently in DB</div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-custom">
                <?php echo $message; ?>
            </div>
            <div class="text-center">
                <a href="books.php" class="btn btn-custom btn-lg">
                    <i class="fas fa-book"></i> View Books
                </a>
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-home"></i> Go to Home
                </a>
            </div>
        <?php else: ?>

            <?php if ($existing > 0): ?>
                <div class="alert alert-warning alert-custom">
                    <strong> Warning:</strong> You already have <?php echo $existing; ?> books in database.
                </div>
            <?php endif; ?>

            <div class="book-preview">
                <h5 class="mb-3">📖 Books to be inserted:</h5>
                <?php foreach (array_slice($myBooks, 0, 5) as $book): ?>
                    <div class="book-item">
                        <h6><?php echo $book['title']; ?></h6>
                        <small><i class="fas fa-user"></i> <?php echo $book['author']; ?> |
                            <i class="fas fa-tag"></i> <?php echo $book['genre']; ?> |
                            <i class="fas fa-dollar-sign"></i> Rs. <?php echo number_format($book['price'], 2); ?></small>
                    </div>
                <?php endforeach; ?>
                <?php if (count($myBooks) > 5): ?>
                    <p class="text-center text-muted mt-3">... and <?php echo count($myBooks) - 5; ?> more books</p>
                <?php endif; ?>
            </div>

            <form method="POST" class="text-center">
                <p class="mb-3">Choose an action:</p>
                <button type="submit" name="action" value="add" class="btn btn-custom btn-lg">
                    Add Books (Keep Existing)
                </button>
                <button type="submit" name="action" value="replace" class="btn btn-danger btn-lg"
                    onclick="return confirm('This will DELETE all existing books! Are you sure?')">
                    Replace All Books
                </button>
                <a href="books.php" class="btn btn-secondary btn-lg">
                    Cancel
                </a>
            </form>

            <div class="alert alert-info alert-custom mt-4">
                <h6>📝 Important Notes:</h6>
                <ul class="mb-0">
                    <li><strong>Add Books</strong>: Keeps existing books and adds new ones</li>
                    <li><strong>Replace All</strong>: Deletes everything and starts fresh</li>
                    <li>Make sure book cover images are in <code>uploads/</code> folder</li>
                    <li> Delete this file after successful insertion</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>