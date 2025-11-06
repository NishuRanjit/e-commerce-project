<?php
session_start();
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
if (!isset($_GET['book_id'])) exit;

$book_id = intval($_GET['book_id']);

// =====================================================
// COSINE SIMILARITY IMPLEMENTATION
// =====================================================

/**
 * Calculate TF-IDF vectors for text
 */
function calculateTFIDF($documents)
{
    $tf = [];
    $df = [];
    $idf = [];
    $tfidf = [];
    $N = count($documents);

    // Tokenize and calculate term frequency
    foreach ($documents as $docId => $text) {
        $words = str_word_count(strtolower($text), 1);
        $tf[$docId] = array_count_values($words);

        // Count document frequency
        $uniqueWords = array_unique($words);
        foreach ($uniqueWords as $word) {
            if (!isset($df[$word])) $df[$word] = 0;
            $df[$word]++;
        }
    }

    // Calculate IDF
    foreach ($df as $word => $count) {
        $idf[$word] = log($N / $count);
    }

    // Calculate TF-IDF
    foreach ($documents as $docId => $text) {
        $tfidf[$docId] = [];
        foreach ($tf[$docId] as $word => $freq) {
            $tfidf[$docId][$word] = $freq * $idf[$word];
        }
    }

    return $tfidf;
}

/**
 * Calculate cosine similarity between two vectors
 */
function cosineSimilarity($vec1, $vec2)
{
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    // Get all unique terms
    $allTerms = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));

    foreach ($allTerms as $term) {
        $val1 = isset($vec1[$term]) ? $vec1[$term] : 0;
        $val2 = isset($vec2[$term]) ? $vec2[$term] : 0;

        $dotProduct += $val1 * $val2;
        $magnitude1 += $val1 * $val1;
        $magnitude2 += $val2 * $val2;
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    if ($magnitude1 == 0 || $magnitude2 == 0) return 0;

    return $dotProduct / ($magnitude1 * $magnitude2);
}

/**
 * Create feature vector combining multiple attributes
 */
function createFeatureVector($book)
{
    // Combine multiple features with weights
    $features = [];

    // Title (weight: 2x)
    $features[] = strtolower($book['title']);
    $features[] = strtolower($book['title']);

    // Author (weight: 3x)
    $features[] = strtolower($book['author']);
    $features[] = strtolower($book['author']);
    $features[] = strtolower($book['author']);

    // Genre (weight: 4x) - most important
    $genre = strtolower($book['genre']);
    for ($i = 0; $i < 4; $i++) {
        $features[] = $genre;
    }

    // Description
    $features[] = strtolower($book['description'] ?? '');

    // Price range as text feature
    $priceRange = '';
    if ($book['price'] < 10) $priceRange = 'budget';
    elseif ($book['price'] < 25) $priceRange = 'affordable';
    elseif ($book['price'] < 50) $priceRange = 'premium';
    else $priceRange = 'luxury';
    $features[] = $priceRange;
    $features[] = $priceRange; // weight 2x

    return implode(' ', $features);
}

// =====================================================
// FETCH BOOKS AND CALCULATE RECOMMENDATIONS
// =====================================================

// Get the clicked book
$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) exit;
$targetBook = $result->fetch_assoc();
$stmt->close();

// Get all other books
$stmt2 = $conn->prepare("SELECT * FROM books WHERE book_id != ?");
$stmt2->bind_param("i", $book_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$books = [];
$documents = [];

// Prepare documents for TF-IDF
$documents['target'] = createFeatureVector($targetBook);

while ($row = $result2->fetch_assoc()) {
    $books[$row['book_id']] = $row;
    $documents[$row['book_id']] = createFeatureVector($row);
}
$stmt2->close();

// Calculate TF-IDF vectors
$tfidfVectors = calculateTFIDF($documents);

// Calculate cosine similarity for each book
$similarities = [];
foreach ($books as $bookId => $bookData) {
    $similarity = cosineSimilarity($tfidfVectors['target'], $tfidfVectors[$bookId]);
    $similarities[$bookId] = [
        'score' => $similarity,
        'book' => $bookData
    ];
}

// Sort by similarity score (descending)
usort($similarities, function ($a, $b) {
    return $b['score'] <=> $a['score'];
});

// Get top 4 recommendations
$recommendations = array_slice($similarities, 0, 4);

// =====================================================
// OUTPUT HTML
// =====================================================

if (count($recommendations) > 0) {
    foreach ($recommendations as $rec) {
        $book = $rec['book'];
        $score = round($rec['score'] * 100, 1); // Convert to percentage

        echo '
        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
            <div class="book-card">
                <div class="book-cover-wrapper">
                    <img src="' . htmlspecialchars($book['cover_image'] ?? 'uploads/placeholder.jpg') . '" 
                         class="book-cover" 
                         alt="' . htmlspecialchars($book['title']) . '"
                         onerror="this.src=\'uploads/placeholder.jpg\'">
                    <span class="book-badge">' . $score . '% Match</span>
                </div>
                <div class="card-body">
                    <h5 class="book-title">' . htmlspecialchars($book['title']) . '</h5>
                    <p class="book-author">
                        <i class="fas fa-user-circle"></i>
                        ' . htmlspecialchars($book['author']) . '
                    </p>
                    <p class="book-description">' . htmlspecialchars($book['description'] ?? 'No description available') . '</p>
                    <div class="book-footer">
                        <div class="book-price">$' . number_format($book['price'], 2) . '</div>
                        <div class="book-actions">
                            <a href="reviews.php?book_id=' . $book['book_id'] . '" 
                               class="btn-review" 
                               title="Write a Review">
                                <i class="fas fa-star"></i>
                            </a>
                            <a href="cart.php?book_id=' . $book['book_id'] . '" 
                               class="btn-cart">
                                <i class="fas fa-cart-plus"></i> <span>Add</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<div class="col-12"><p class="text-muted text-center">No similar books found.</p></div>';
}

$conn->close();
