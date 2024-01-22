<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Companies Cin Details</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<script>
    function goBack() {
        window.history.back();
    }
</script>
<body style='background-color:#f0f2f8;'>

<?php
// Error reporting
error_reporting(E_ERROR | E_PARSE);

// Start session
session_start();

include 'nav.php';

include 'config.php';


// Get the CIN value from the query parameter (with basic input validation)
$cin = isset($_GET['cin']) ? $conn->real_escape_string($_GET['cin']) : '';

// Get the referring URL (with basic input validation)
$referrer = isset($_SERVER['HTTP_REFERER']) ? $conn->real_escape_string($_SERVER['HTTP_REFERER']) : 'company.php';

// Query 1: Company Details
$sql = "SELECT COMPANY_NAME AS 'Company Name', CIN, Status, DATE_OF_REGISTRATION AS 'Date Of Registration', ROC, AUTHORIZED_CAPITAL AS 'AUTHORIZED CAPITAL', PAIDUP_CAPITAL AS 'PAIDUP CAPITAL', CATEGORY, SUBCATEGORY, TYPE_OF_COMPANY AS 'TYPE OF COMPANY', Pincode FROM  technowire_data.CIN WHERE COMPANY_NAME = ? OR CIN = ? OR Pincode = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $cin, $cin, $cin);
$stmt->execute();
$result = $stmt->get_result();
$output = '<div class="mt-3"><div class="row">';
while ($row = $result->fetch_assoc()) {
    $output .= '<div class="card" style="margin:10px;padding:10px;">'; // Add the card div
    foreach ($row as $key => $value) {
        $output .= '<div class="row">'; // Add the row div
        $output .= '<div class="col-md-6"><strong>' . htmlspecialchars($key) . '</strong></div>';
        $output .= '<div class="col-md-6">' . htmlspecialchars($value) . '</div>';
        $output .= '</div>'; // Close the row div
    }
    $output .= '</div>'; // Close the card div
}
$output .= '</div></div>';

// Query 2: DIN Details
$queryDINDetails = "SELECT DIN, NAME, DATE_JOIN, DESIGNATION FROM technowire_data.CIN_DIN WHERE CIN = ?";
$stmtDINDetails = $conn->prepare($queryDINDetails);
$stmtDINDetails->bind_param("s", $cin);
$stmtDINDetails->execute();
$resultDINDetails = $stmtDINDetails->get_result();
$dinTable = generateTable($resultDINDetails);

// Query 3: Charges Details
$queryChargesDetails = "SELECT * FROM technowire_data.CIN_CHARGES WHERE CIN = ?";
$stmtChargesDetails = $conn->prepare($queryChargesDetails);
$stmtChargesDetails->bind_param("s", $cin);
$stmtChargesDetails->execute();
$resultChargesDetails = $stmtChargesDetails->get_result();
$chargesTable = generateTable($resultChargesDetails, true); // Display headers when no data is found

// Display tables
echo '<div class="container mt-3">';
echo '
    <div class="container mt-3">
        <h2>Company</h2>
            <div class="card-body">' . $output . '</div>
    </div>

    <div class="container mt-3">
        <h2>Director</h2>
            <div class="card-body">' . $dinTable . '</div>
    </div>

    <div class="container mt-3">
        <h2>Chargers</h2>
            <div class="card-body">' . $chargesTable . '</div>
    </div>';
// "Go Back" button
echo '<div class="row">';
echo ' <button onclick="goBack()" class="btn btn-primary" style="width:300px;">Go Back</button>';

// "Know More" button
echo '<div class="col-md-4"><br><a href="https://finanvo.in/company/profile/' . urlencode($row['CIN']) . '/' . urlencode($row['Company Name']) . '" class="btn btn-success">Know More</a></div>';
echo '</div>';  // Close the row
echo '</div>';
echo '<br><br><br><br>';

// Close the prepared statements and database connection
$stmt->close();
$stmtDINDetails->close();
$stmtChargesDetails->close();
$conn->close();

// Function to generate table HTML
function generateTable($result, $displayHeaders = false)
{
    $tableHTML = '<table class="table table-bordered table-striped">';

    $headerPrinted = false;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!$headerPrinted) {
                $tableHTML .= '<thead class="table-dark">';
                $tableHTML .= '<tr>';
                foreach ($row as $key => $value) {
                    $tableHTML .= '<th scope="col">' . htmlspecialchars($key) . '</th>';
                }
                $tableHTML .= '</tr>';
                $tableHTML .= '</thead>';
                $tableHTML .= '<tbody>';
                $headerPrinted = true;
            }

            $tableHTML .= '<tr>';
            foreach ($row as $key => $value) {
                if ($key === 'DIN') {
                    // Add hyperlink to DIN values
                    $tableHTML .= '<td><a href="din_details.php?din=' . urlencode($value) . '">' . htmlspecialchars($value) . '</a></td>';
                } else {
                    $tableHTML .= '<td>' . htmlspecialchars($value) . '</td>';
                }
            }
            $tableHTML .= '</tr>';
        }
    } else {
        // Display an empty row with a message if no records are found
        $tableHTML .= '<tbody>';
        $tableHTML .= '<tr><td colspan="2">No Records Found</td></tr>';
    }

    $tableHTML .= '</tbody>';
    $tableHTML .= '</table>';

    return $tableHTML;
}
?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script async src="https://pagead2.googlesyndication.com/pagead/js?client=ca-pub-2104302062002302" crossorigin="anonymous"></script>
 <br> <br> <br>
<?php include 'footer.php';?>

</body>

</html>
