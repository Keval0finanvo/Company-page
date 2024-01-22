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
    <script>
        // JavaScript to set the selected values of the dropdowns
        document.addEventListener("DOMContentLoaded", function () {
            var monthDropdown = document.getElementById("month");
            var yearDropdown = document.getElementById("year");

            // Check if the values are set in local storage
            var selectedMonth = localStorage.getItem("selectedMonth");
            var selectedYear = localStorage.getItem("selectedYear");

            // Set the values if they exist
            if (selectedMonth !== null) {
                monthDropdown.value = selectedMonth;
            }

            if (selectedYear !== null) {
                yearDropdown.value = selectedYear;
            }

            // Add event listener to update local storage when dropdown values change
            monthDropdown.addEventListener("change", function () {
                localStorage.setItem("selectedMonth", monthDropdown.value);
            });

            yearDropdown.addEventListener("change", function () {
                localStorage.setItem("selectedYear", yearDropdown.value);
            });
        });
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <title>Search Companies by date</title>
</head>
<body>
    <?php include 'nav.php';?>
    <div class="container">
        <form method="post">
            <label for="month"><h4>Select Month: </h4></label>
            <select name="month" id="month">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                    echo "<option value='$month'>$month </option>";
                }
                ?>
            </select>

            <label for="year"><h4>Select Year: </h4></label>
            <select name="year" id="year">
                <?php
                $currentYear = date('Y');
                for ($i = $currentYear; $i >= $currentYear - 100; $i--) {
                    echo "<option value='$i'>$i </option>";
                }
                ?>
            </select>

            <button type="submit">Search</button>
        </form>

        <?php
        // Your database connection details (as you've included them in config.php)
        include 'config.php';

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

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

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedMonth = $_POST['month'];
            $selectedYear = $_POST['year'];

            // Validate input (you may need additional validation)
            $selectedMonth = intval($selectedMonth);
            $selectedYear = intval($selectedYear);

            $resultPerPage = 15;
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $start_from = ($page - 1) * $resultPerPage;

            $stmt = $conn->prepare("SELECT CIN, COMPANY_NAME, DATE_OF_REGISTRATION, REGISTERED_OFFICE_ADDRESS
                    FROM technowire_data.CIN_CHARGES
                    WHERE MONTH(STR_TO_DATE(DATE_OF_REGISTRATION, '%d-%m-%y')) = ? 
                    AND YEAR(STR_TO_DATE(DATE_OF_REGISTRATION, '%d-%m-%y')) = ? 
                    LIMIT ?, ?");
            $stmt->bind_param("iiii", $selectedMonth, $selectedYear, $start_from, $resultPerPage);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $headerPrinted = false;

            if ($result && $result->num_rows > 0) {
                echo '<div class="mt-3"><h3>Result</h3><table class="table table-bordered table-striped">';

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

                $total_pages = ceil($result->num_rows / $resultPerPage);

                generatePaginationLinks($page, $total_pages);
            } else {
                echo '<div class="mt-3"><p>No results found.</p></div>';
            }
        }

        // Close the database connection when done
        $conn->close();
        ?>
    </div>
    <br> <br> <br>
    <?php include 'footer.php';?>
</body>
</html>
