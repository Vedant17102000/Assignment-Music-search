<?php
// Read the JSON file
$jsonFile = 'artists.json';
$artists = [];

if (file_exists($jsonFile) && is_readable($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $artists = json_decode($jsonData, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artists Table</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Artists Table</h1>

<!-- Search Bar with Dropdown for selecting search criteria -->
<div>
    <label for="searchBy">Search by:</label>
    <select id="searchBy">
        <option value="name">Name</option>
        <option value="genre">Genre</option>
        <option value="location">Location</option>
    </select>

    <input type="text" id="searchInput" onkeyup="searchArtist()" placeholder="Search...">
    <button onclick="searchArtist()">Search</button>
</div>

<!-- Display Suggestions -->
<div id="suggestions" style="margin-top: 10px;"></div>

<!-- Artists Table -->
<table id="artistsTable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Genre</th>
            <th>Profile Picture</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody id="artistsTableBody">
        <!-- Artist data will be inserted here by PHP -->
        <?php foreach ($artists as $index => $artist): ?>
            <tr id="row-<?php echo $index; ?>">
                <td><?php echo htmlspecialchars($artist['name']); ?></td>
                <td><?php echo htmlspecialchars($artist['genre']); ?></td>
                <td><img src="<?php echo htmlspecialchars($artist['profile_picture']); ?>" alt="Profile Picture" width="50" height="50"></td>
                <td><?php echo htmlspecialchars($artist['location']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// Function to search through the table by the selected field (name, genre, location)
function searchArtist() {
    const query = document.getElementById("searchInput").value;
    const searchBy = document.getElementById("searchBy").value;
    const suggestionsDiv = document.getElementById("suggestions");

    if (query.length > 0) {
        // Make an API request to search.php
        fetch(`search.php?query=${query}&searchBy=${searchBy}`)
            .then(response => response.json())
            .then(data => {
                // Clear previous suggestions
                suggestionsDiv.innerHTML = '';

                // Show suggestions if no results found
                if (data.suggestions.length > 0) {
                    const suggestionList = document.createElement('ul');
                    data.suggestions.forEach(suggestion => {
                        const listItem = document.createElement('li');
                        listItem.textContent = suggestion;
                        listItem.style.cursor = 'pointer';
                        listItem.onclick = function() {
                            document.getElementById("searchInput").value = suggestion;
                            searchArtist(); // Perform search on suggestion click
                        };
                        suggestionList.appendChild(listItem);
                    });
                    suggestionsDiv.appendChild(suggestionList);
                } else {
                    suggestionsDiv.innerHTML = ''; // No suggestions if there are matches
                }

                // If one match is found, scroll to that row
                if (data.matchFound && data.results.length === 1) {
                    const rowIndex = 0; // Only one match
                    const row = document.getElementById(`row-${rowIndex}`);
                    row.scrollIntoView({behavior: "smooth", block: "center"});
                    row.style.backgroundColor = "#f2f2f2"; // Highlight the row
                } else if (data.matchFound && data.results.length > 1) {
                    // If multiple matches, redirect to the results page
                    const queryString = new URLSearchParams({
                        query: query,
                        searchBy: searchBy
                    }).toString();
                    window.location.href = `search_results.php?${queryString}`;
                } else {
                    alert('No matches found.');
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        // Clear suggestions when the input is empty
        suggestionsDiv.innerHTML = '';
    }
}
</script>

</body>
</html>
