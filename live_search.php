<!-- =====================================================
     LIVE SEARCH AJAX - live_search.php
     This shows instant search results as user types
     ===================================================== -->

<?php
// live_search.php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (strlen($query) >= 2) { // Only search if 2+ characters
    $search_term = "%{$query}%";

    $sql = "SELECT book_id, title, author, price, cover_image, genre 
            FROM books 
            WHERE title LIKE ? 
               OR author LIKE ? 
               OR genre LIKE ?
            ORDER BY 
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN author LIKE ? THEN 2
                    ELSE 3
                END
            LIMIT 8";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($results);
$conn->close();
?>

<!-- =====================================================
     LIVE SEARCH HTML/JS COMPONENT
     Add this to your books.php or any page
     ===================================================== -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Live Search Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4f9ff, #eefaf1);
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }

        .live-search-container {
            max-width: 700px;
            margin: 50px auto;
            position: relative;
        }

        .search-input-wrapper {
            position: relative;
            background: white;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .search-input-wrapper input {
            width: 100%;
            border: none;
            padding: 18px 60px 18px 30px;
            font-size: 16px;
            outline: none;
        }

        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }

        .loading-spinner {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            margin-top: 10px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            max-height: 500px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }

        .search-results.show {
            display: block;
        }

        .result-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .result-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-image {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .result-details {
            flex: 1;
        }

        .result-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .result-author {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .result-genre {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
        }

        .result-price {
            font-weight: bold;
            color: #e74c3c;
            font-size: 1.1rem;
        }

        .no-results {
            padding: 30px;
            text-align: center;
            color: #999;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-spinner i {
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body>

    <div class="live-search-container">
        <h2 class="text-center mb-4">🔍 Live Book Search</h2>

        <div class="search-input-wrapper">
            <input
                type="text"
                id="liveSearchInput"
                placeholder="Start typing to search books..."
                autocomplete="off">
            <i class="fas fa-search search-icon"></i>
            <div class="loading-spinner">
                <i class="fas fa-spinner"></i>
            </div>
        </div>

        <div class="search-results" id="searchResults"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let searchTimeout;
        const searchInput = $('#liveSearchInput');
        const searchResults = $('#searchResults');
        const searchIcon = $('.search-icon');
        const loadingSpinner = $('.loading-spinner');

        searchInput.on('input', function() {
            const query = $(this).val().trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                searchResults.removeClass('show').empty();
                return;
            }

            // Show loading
            searchIcon.hide();
            loadingSpinner.show();

            // Debounce search (wait 300ms after user stops typing)
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 300);
        });

        function performSearch(query) {
            $.ajax({
                url: 'live_search.php',
                type: 'GET',
                data: {
                    q: query
                },
                dataType: 'json',
                success: function(data) {
                    displayResults(data, query);
                    loadingSpinner.hide();
                    searchIcon.show();
                },
                error: function() {
                    searchResults.html('<div class="no-results">Error loading results</div>').addClass('show');
                    loadingSpinner.hide();
                    searchIcon.show();
                }
            });
        }

        function displayResults(results, query) {
            if (results.length === 0) {
                searchResults.html('<div class="no-results">No books found</div>').addClass('show');
                return;
            }

            let html = '';
            results.forEach(function(book) {
                // Highlight matching text
                const titleHighlighted = highlightMatch(book.title, query);
                const authorHighlighted = highlightMatch(book.author, query);

                html += `
                    <a href="cart.php?book_id=${book.book_id}" class="result-item">
                        <img src="${book.cover_image || 'uploads/placeholder.jpg'}" 
                             class="result-image" 
                             alt="${escapeHtml(book.title)}"
                             onerror="this.src='uploads/placeholder.jpg'">
                        <div class="result-details">
                            <div class="result-title">${titleHighlighted}</div>
                            <div class="result-author"><i class="fas fa-user"></i> ${authorHighlighted}</div>
                            ${book.genre ? `<span class="result-genre">${escapeHtml(book.genre)}</span>` : ''}
                        </div>
                        <div class="result-price">$${parseFloat(book.price).toFixed(2)}</div>
                    </a>
                `;
            });

            searchResults.html(html).addClass('show');
        }

        function highlightMatch(text, query) {
            const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
            return escapeHtml(text).replace(regex, '<span class="highlight">$1</span>');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function escapeRegex(text) {
            return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Close results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.live-search-container').length) {
                searchResults.removeClass('show');
            }
        });

        // Reopen results when clicking on input (if there are results)
        searchInput.on('click', function() {
            if (searchResults.children().length > 0) {
                searchResults.addClass('show');
            }
        });
    </script>
</body>

</html>