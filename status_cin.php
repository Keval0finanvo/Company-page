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
    <title>Companies by status</title>
<style></style>
</head>
<?php include 'nav.php';?>

<div class="container-fluid mt-3 ">
    <div class="row" id='row'>
        <div class="col-12" id='main-col'>
            <div class="grid-container">
                <?php
                include 'config.php';
                $selectedButtonValue = '';
                $query = "SELECT DISTINCT Status FROM technowire_data.CIN";
                $result = $conn->query($query);

                while ($row = $result->fetch_assoc()) {
                    $states[] = $row['Status'];
                }

                $limit = 10;
                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (isset($_POST["submittedButton"])) {
                        $selectedButtonValue = $_POST["submittedButton"];
                        $sql = "SELECT cin, COMPANY_NAME, REGISTERED_OFFICE_ADDRESS FROM technowire_data.CIN WHERE Status = '$selectedButtonValue'";
                    } else {
                        $sql = "SELECT cin, COMPANY_NAME, REGISTERED_OFFICE_ADDRESS FROM technowire_data.CIN WHERE Status IS NOT NULL";
                    }
                } else {
                    $sql = "SELECT cin, COMPANY_NAME, REGISTERED_OFFICE_ADDRESS FROM technowire_data.CIN WHERE Status IS NOT NULL";
                }

                $result = $conn->query($sql);
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $total_records = count($rows);
                $total_pages = ceil($total_records / $limit);

                $records_on_page = array_slice($rows, $offset, $limit);

                echo '<table class="table table-bordered">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>CIN</th>';
                echo '<th>COMPANY_NAME</th>';
                echo '<th>REGISTERED_OFFICE_ADDRESS</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                foreach ($records_on_page as $row) {
                    echo '<tr>';
                    echo '<td>';
                    echo '<a class="link-opacity-10" href="cin_details.php?cin=' . urlencode($row['cin']) . '" style="text-decoration: underline !important; color: blue !important;">';
                    echo $row['cin'];
                    echo '</a>';
                    echo '</td>';
                    echo '<td>' . $row['COMPANY_NAME'] . '</td>';
                    echo '<td>' . $row['REGISTERED_OFFICE_ADDRESS'] . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';

                generatePagination($page, $total_pages, $selectedButtonValue);
                ?>
            </div>
            <br>
        </div>
        <div class="col-12" id='main-col'>
            <form method="post" id="state-form">
                <?php
                foreach ($states as $state) {
                    echo '<button class="btn btn-primary state-button" style="margin: 5px; background-color:#645df9;color:#ffffff;" name="submittedButton" type="submit" value="' . $state . '">' . $state . '</button>';
                }
                $conn->close();
                ?>
                <input type="hidden" id="selected-state" name="state" value="">
            </form>
        </div>
    </div>
</div>
<br> <br> <br>

<?php include 'footer.php';?>
</body>
</html>

<?php
function generatePagination($page, $total_pages, $selectedButtonValue)
{
    echo "<div id='pagination'>";
    echo "<nav aria-label='Page navigation example'>";
    echo "<ul class='pagination justify-content-center'>";

    $num_links = 5;

    if ($page > 1) {
        echo "<li class='page-item'><a class='page-link' style='background-color:#645df9;color:#ffffff' href='?page=" . ($page - 1) . "&submittedButton=" . $selectedButtonValue . "'>Previous</a></li>";
    }

    $start = max(1, $page - $num_links);
    $end = min($total_pages, $start + 2 * $num_links);

    for ($i = $start; $i <= $end; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        echo "<li class='page-item $activeClass'><a class='page-link' href='?page=$i&submittedButton=$selectedButtonValue'>$i</a></li>";
    }

    if ($page < $total_pages) {
        echo "<li class='page-item'><a class='page-link' style='background-color:#645df9;color:#ffffff' href='?page=" . ($page + 1) . "&submittedButton=" . $selectedButtonValue . "'>Next</a></li>";
    }

    echo "</ul>";
    echo "</nav>";
    echo "</div>";
}
?>