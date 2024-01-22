<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Companies Director details</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<script>
    function goBack() {
        window.history.back();
    }
</script>
<body style='background-color:#f0f2f8;'>

<?php include 'nav.php'; ?>

<?php
include 'config.php';

function generateTable($result, $displayHeaders = false)
{
    $tableHTML = '<table class="table table-bordered table-striped">';

    $headerPrinted = false;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!$headerPrinted) {
                $tableHTML .= '<thead class="table-dark"><tr>';
                foreach ($row as $key => $value) {
                    $tableHTML .= '<th scope="col">' . htmlspecialchars($key) . '</th>';
                }
                $tableHTML .= '</tr></thead><tbody>';
                $headerPrinted = true;
            }

            $tableHTML .= '<tr>';
            foreach ($row as $key => $value) {
                if ($key === 'CIN') {
                    $cinLink = '<a href="cin_details.php?cin=' . urlencode($value) . '">' . htmlspecialchars($value) . '</a>';
                    $tableHTML .= '<td>' . $cinLink . '</td>';
                } else {
                    $tableHTML .= '<td>' . htmlspecialchars($value) . '</td>';
                }
            }
            $tableHTML .= '</tr>';
        }
    } else {
        $tableHTML .= '<tbody><tr><td colspan="2">No Records Found</td></tr>';
    }

    $tableHTML .= '</tbody></table>';
    return $tableHTML;
}

// Get the DIN value from the query parameter (with basic input validation)
$din = isset($_GET['din']) ? $_GET['din'] : '';
$din = $conn->real_escape_string($din);

// Run the SQL query to get DIN details
$dinQuery = "SELECT * FROM technowire_data.CIN_CHARGES WHERE DIN = ?";
$dinStmt = $conn->prepare($dinQuery);
$dinStmt->bind_param('s', $din);
$dinStmt->execute();
$dinResult = $dinStmt->get_result();

// Run the SQL query to get associated CIN from DIN
$cinQuery = "SELECT CIN FROM technowire_data.CIN_DIN WHERE DIN = ? GROUP BY CIN";
$cinStmt = $conn->prepare($cinQuery);
$cinStmt->bind_param('s', $din);
$cinStmt->execute();
$cinResult = $cinStmt->get_result();

// Check if CIN is found
if ($cinResult && $cinResult->num_rows > 0) {
    $cinRow = $cinResult->fetch_assoc();
    $cin = $cinRow['CIN'];

    // Run the SQL query to get charges details for the associated CIN
    $chargesQuery = "SELECT * FROM technowire_data.CIN_CHARGES WHERE CIN = ?";
    $chargesStmt = $conn->prepare($chargesQuery);
    $chargesStmt->bind_param('s', $cin);
    $chargesStmt->execute();
    $chargesResult = $chargesStmt->get_result();
}

$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'company.php';
error_reporting(E_ERROR | E_PARSE);

// HTML Output
printf('<div class="container mt-3"><div class="row"><div class="col-12"><h2>DIN Details</h2>%s</div>',
    generateTable($dinResult, true));

printf('<div class="col-12"><br><h2>Charges Details</h2>%s<div class="col-md-12"><br></div>',
    generateTable($chargesResult, true), htmlspecialchars($referrer));

printf('<div class="col-md-12"><br><a href="https://finanvo.in/director/profile/%s/%s" class="btn btn-success">Know More</a></div></div></div></div>',
    urlencode($din), urlencode($row['NAME']));

echo ' <button onclick="goBack()" class="btn btn-primary" style="width:300px;">Go Back</button>';


// Close the database connection
$conn->close();
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script async src="https://pagead2.googlesyndication.com/pagead/js?client=ca-pub-2104302062002302" crossorigin="anonymous"></script>
 <br> <br> <br> <br>

<?php include 'footer.php'; ?>

</body>
</html>
