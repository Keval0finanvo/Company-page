<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <title>Find All companies</title>
    
</head>
<body>
    <?php include 'nav.php';?>
    <div class="container">
        <?php
            include 'config.php';

            // Number of records per page
            $records_per_page = 20;

            // Get the current page number from the query string
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

            // Calculate the offset for the query
            $offset = ($page - 1) * $records_per_page;

            // SQL Query
            $query = "SELECT CIN, COMPANY_NAME, DATE_OF_REGISTRATION, REGISTERED_OFFICE_ADDRESS FROM technowire_data.CIN LIMIT $offset, $records_per_page";
            $result = $conn->query($query);

            // Display the result in a table
            if ($result && $result->num_rows > 0) {
                echo '<div class="mt-3"><h2>All companies</h2><table class="table table-bordered table-striped">';
                $headerPrinted = false;

                while ($row = $result->fetch_assoc()) {
                    if (!$headerPrinted) {
                        echo '<tr>';
                        foreach ($row as $key => $value) {
                            echo '<th class="table-dark">' . htmlspecialchars($key) . '</th>';
                        }
                        echo '</tr>';
                        $headerPrinted = true;
                    }

                    // Output table rows with hyperlinks
                    echo '<tr>';
                    foreach ($row as $key => $value) {
                        if ($key === 'CIN') {
                            echo '<td><a href="cin_details.php?cin=' . urlencode($value) . '">' . htmlspecialchars($value) . '</a></td>';
                        } else {
                            echo '<td>' . htmlspecialchars($value) . '</td>';
                        }
                    }
                    echo '</tr>';
                }

                echo '</table>';

                // Pagination
                echo "<br>";
                echo "<div id='pagination'>";
                echo "<nav aria-label='Page navigation example'>";
                echo "<ul class='pagination justify-content-center'>";

                // Previous button
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' style='background-color:#645df9;color:#ffffff' href='?page=" . ($page - 1) . "'>Previous</a></li>";
                }

                // Calculate total pages
                $total_pages = ceil($result->num_rows / $records_per_page);

                $num_links = 5; // Number of links to show before and after the current page
                $start = max(1, $page - $num_links);
                $end = min($total_pages, $start + 2 * $num_links);

                for ($i = $start; $i <= $end; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $activeClass'><a class='page-link' href='?page=$i'>$i</a></li>";
                }

                // Next button
                if ($page < $total_pages) {
                    echo "<li class='page-item'><a class='page-link'  style='background-color:#645df9;color:#ffffff' href='?page=" . ($page + 1) . "'>Next</a></li>";
                }

                echo "</ul>";
                echo "</nav>";
                echo "</div>";
            }
        ?>
    </div>
     <br> <br> <br>
    <?php include 'footer.php';?>
</body>
</html>
