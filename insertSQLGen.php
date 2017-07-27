<?php
define("DB_HOST", "set THIS!");
define("DB_PORT", "set THIIS!");
define("DB_SID", "set THIS!");
define("DB_USERNAME", "set THIS!");
define("DB_PASSWORD", "set THIS!");

$maxrow = 1000; //<-- set the total number of columns (ex:SELECT COUNT(*) FROM TABLE_NAME)
$tmp = strlen($maxrow);

switch ($tmp) {
case 8:
    $div = $tmp * 8;
    break;
case 7:
    $div = $tmp * 7;
    break;
case 5:
    $div = $tmp * 5;
    break;
case 4:
    $div = $tmp * 4;
    break;
default:
    $div = 2;
}

$steprow = floor(abs($maxrow / $div));

function rowfunc($setrow,$step,$max)
{
  // SQL Definition
  $insertTable = 'set insert table name';
  $start = $setrow;

  if(($start + $step) >= $max){
    $end = $max;
  }else {
    $end = ($start + $step);
  }

  // SQL Query Paging
  $setter ="SELECT * FROM (SELECT ROWNUM AS RNUM, A.* FROM (".$insertTable.") A WHERE ROWNUM <=".$end.") WHERE RNUM >".$start." ";
  return $setter;
}

function sqlrun($input){
  $sql = $input;
  // Access to DB
  try {
      $dbh = new PDO("oci:dbname=//".DB_HOST.":".DB_PORT."/".DB_SID.";charset=UTF8", DB_USERNAME, DB_PASSWORD);
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
      $message = "DB connection failure\n";
      echo date('Y/m/d H:i:s ').$message.' : '.$e->getMessage()."\n";
      exit;
  }

  // SQL execution
  try{
      $select = $dbh->prepare($sql);
      $select->execute();
      $results = $select->fetchAll(PDO::FETCH_ASSOC);
  }catch (Exception $ex) {
      $message = "Query failure\n";
      echo date('Y/m/d H:i:s ').$message.' : '.$ex->getMessage()."\n";
      exit;
  }

  // Generate INSERT statement
  foreach ($results as $result) {
      foreach ($result as $column => $value) {
          if (!empty($value)) {
              if (isset($columSpace)) {
                  $columSpace .= ",".$column;
              } else {
                  $columSpace = $column;
              }
              if (isset($valueSpace)) {
                  $valueSpace .= ",'".$value."'";
              } else {
                  $valueSpace = "'".$value."'";
              }
          }
      }
      // SQL output
      echo "INSERT INTO ".$insertTable." (".$columSpace.") VALUES (".$valueSpace.");\n";

      // Initialize
      unset($columSpace);
      unset($valueSpace);
  }
}

for($count = 1 ; $count < $maxrow ; $count = ($count + $steprow)){
    $input = rowfunc($count,$steprow,$maxrow);
    sqlrun($input);
}
?>
