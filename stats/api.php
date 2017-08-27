<?
include("../config.php");

header('Content-Type: application/json');
echo json_encode(getStats());

function getStats() {
    $results = array();
    $rowNames = array();
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_ANONDATA);

    if (!$conn) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT scopes FROM ".TABLE_DATA;
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            if(empty($row['scopes'])) {
                if(array_key_exists("", $results)) {
                    $results[""]++;
                } else {
                    $results[""] = 1;
                }
            } else {
                $scopes = explode(',', $row['scopes']);
                foreach($scopes as $scope) {
                    if(array_key_exists($scope, $results)) {
                        $results[$scope]++;
                    } else {
                        $results[$scope] = 1;
                        array_push($rowNames, $scope);
                    }
                }
            }
        }
    }
    mysqli_close($conn);

    return array('scopes' => $rowNames, 'data' => array_values($results));
}

?>