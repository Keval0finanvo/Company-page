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
    <title>GST Numbers with Pagination</title>

</head>
<style></style>

<body>
    <?php include 'nav.php';?>
    <div class="container mt-3">
        <h2>Company Search Form</h2>
        <form action="" method="get">
            <div class="mb-3 mt-3">
                <label for="search_company">Search for a Company:</label>
                <input type="text" class="form-control" id="search_company" placeholder="Search for a Company" name="search" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label class="d-block">Search Type:</label>
                <?php
                $searchTypes = ['company', 'pincode', 'din', 'city', 'category','state' ,'roc'];
                foreach ($searchTypes as $type) {
                    $isChecked = isset($_GET['search_type']) && $_GET['search_type'] === $type ? 'checked' : '';
                    echo "<div class='form-check form-check-inline'>
                            <input class='form-check-input' type='radio' name='search_type' id='{$type}_radio' value='{$type}' {$isChecked}>
                            <label class='form-check-label' for='{$type}_radio'>" . strtoupper($type) . "</label>
                          </div>";
                }
                ?>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <?php
        $resultTable = ''; // Variable to store the table HTML

        $limit = 10;

        function generatePagination($currentPage, $totalPages, $searchType, $search)
        {
            echo "<div class='pagination'>";
            echo "<nav aria-label='Page navigation example'>";
            echo "<ul class='pagination'>";

            $num_links = 5;

            if ($currentPage > 1) {
                echo "<li class='page-item'><a class='page-link' href='?page=" . ($currentPage - 1) . "&search_type=$searchType&search=$search'>Previous</a></li>";
            }

            $start = max(1, $currentPage - $num_links);
            $end = min($totalPages, $start + 2 * $num_links);

            for ($i = $start; $i <= $end; $i++) {
                echo "<li class='page-item " . ($i == $currentPage ? 'active' : '') . "'><a class='page-link' href='?page=$i&search_type=$searchType&search=$search'>$i</a></li>";
            }

            if ($currentPage < $totalPages) {
                echo "<li class='page-item'><a class='page-link' href='?page=" . ($currentPage + 1) . "&search_type=$searchType&search=$search'>Next</a></li>";
            }

            echo "</ul>";
            echo "</nav>";
            echo "</div>";
        }

        if (isset($_GET['search_type']) && isset($_GET['search'])) {
            $search = $_GET['search'];
            $searchType = $_GET['search_type'];
            include 'config.php';

            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            switch ($searchType) {
                case 'company':
                    if (is_numeric($search)) {
                        $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` FROM technowire_data.CIN WHERE cin = '$search'";
                    } else {
                        $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` FROM technowire_data.CIN WHERE company_name LIKE '$search%'";
                    }
                    break;
                case 'pincode':
                    $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` , Pincode FROM technowire_data.CIN WHERE pincode = $search";
                    break;
                case 'din':
                    $query = "
                        SELECT d.name AS 'Name', c.company_name AS 'Company Name', d.din AS 'Din', c.cin AS 'Cin'  ,c.REGISTERED_OFFICE_ADDRESS AS 'REGISTERED OFFICE ADDRESS'
                        FROM technowire_data.CIN AS c
                        JOIN din AS d ON c.CIN = d.CIN
                        WHERE d.din = '$search'
                        GROUP BY c.cin, c.company_name, d.din, c.REGISTERED_OFFICE_ADDRESS";
                    break;
                case 'city':
                    $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` , City FROM technowire_data.CIN WHERE city = '$search' or city LIKE '$search%'";
                    break;
                case 'category':
                    $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` , category FROM technowire_data.CIN WHERE category = '$search'";
                    break;
                case 'state':
                    $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` , STATE FROM technowire_data.CIN WHERE state = '$search' or  state LIKE '$search%'";
                    break;
                case 'roc':
                    if (stripos($search, 'roc') === false) {
                        $search = 'roc ' . $search;
                    }
                    $query = "SELECT cin AS CIN, company_name AS `Company Name`, REGISTERED_OFFICE_ADDRESS AS `REGISTERED OFFICE ADDRESS` FROM technowire_data.CIN WHERE roc = '$search'";
                    break;
                default:
                    die('Invalid search type');
            }

            $result = $conn->query($query);

            $totalRecords = $result->num_rows;
            $totalPages = ceil($totalRecords / $limit);

            $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($currentPage - 1) * $limit;

            $query .= " LIMIT $limit OFFSET $offset";

            $result = $conn->query($query);

            $headerPrinted = false;
            if ($result && $result->num_rows > 0) {
                $resultTable .= '<div class="mt-3"><h2>Result</h2><table class="table">';

                while ($row = $result->fetch_assoc()) {
                    if (!$headerPrinted) {
                        $resultTable .= '<tr>';
                        foreach ($row as $key => $value) {
                            $resultTable .= '<th>' . htmlspecialchars($key) . '</th>';
                        }
                        $resultTable .= '</tr>';
                        $headerPrinted = true;
                    }

                    $resultTable .= '<tr>';
                    foreach ($row as $key => $value) {
                        if ($key === 'CIN') {
                            $resultTable .= '<td><a href="cin_details.php?cin=' . urlencode($value) . '">' . htmlspecialchars($value) . '</a></td>';
                        } else {
                            $resultTable .= '<td>' . htmlspecialchars($value) . '</td>';
                        }
                    }
                    $resultTable .= '</tr>';
                }

                $resultTable .= '</table>';
            } else {
                $resultTable .= '<h3>No records available</h3>';
            }

            $resultTable .= '</div>';

            echo '<br>';
            generatePagination($currentPage, $totalPages, $searchType, $search);

            $conn->close();
        }
        ?>
        <!-- Display the result stored in the variable -->
        <?php echo $resultTable; ?>
        <br><br><br>
        <?php include 'footer.php';?>
    </div>
</body>
</html>
