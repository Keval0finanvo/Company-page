<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <title>Newest Companies</title>
</head>
<body>
    <?php include 'nav.php';?>
    <div class="container">
        <?php
            include 'config.php';
            // Function to create pagination links
            function generatePaginationLinks($page, $total_pages)
            {
                echo "<br>";
                echo "<div id='pagination'>";
                echo "<nav aria-label='Page navigation example'>";
                echo "<ul class='pagination justify-content-center'>";

                $num_links = 5;

                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' style='background-color:#645df9;color:#ffffff' href='?page=" . ($page - 1) . "'>Previous</a></li>";
                }

                $start = max(1, $page - $num_links);
                $end = min($total_pages, $start + 2 * $num_links);

                for ($i = $start; $i <= $end; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $activeClass'><a class='page-link' href='?page=$i'>$i</a></li>";
                }

                if ($page < $total_pages) {
                    echo "<li class='page-item'><a class='page-link'  style='background-color:#645df9;color:#ffffff' href='?page=" . ($page + 1) . "'>Next</a></li>";
                }

                echo "</ul>";
                echo "</nav>";
                echo "</div>";
            }

            // Assuming your database connection is established in $conn
            $resultPerPage = 15;
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $start_from = ($page - 1) * $resultPerPage;

            $query = "SELECT CIN, COMPANY_NAME, DATE_OF_REGISTRATION, REGISTERED_OFFICE_ADDRESS 
                    FROM technowire_data.CIN
                    ORDER BY STR_TO_DATE(DATE_OF_REGISTRATION, '%d-%m-%y') DESC 
                    LIMIT $start_from, $resultPerPage";
            $result = $conn->query($query);

            // Display results in a table
            $headerPrinted = false;

            if ($result && $result->num_rows > 0) {
                echo '<div class="mt-3"><h2>Latest Companies</h2><table class="table table-bordered table-striped">';

                while ($row = $result->fetch_assoc()) {
                    if (!$headerPrinted) {
                        echo '<tr>';
                        foreach ($row as $key => $value) {
                            echo '<th class="table-dark">' . htmlspecialchars($key) . '</th>';
                        }
                        echo '</tr>';
                        $headerPrinted = true;
                    }

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

                // Calculate total pages
                $total_pages = ceil($result->num_rows / $resultPerPage);

                generatePaginationLinks($page, $total_pages);
            } else {
                echo '<div class="mt-3"><p>No results found.</p></div>';
            }
        ?>
        <br> <br> <br>
        <?php include 'footer.php';?>
    </div>
</body>
</html>
