<?php
header("Content-type: text/xml");

$opts = getopt('t:c:o:w:h');
foreach (array_keys($opts) as $opt) {
    switch ($opt) {
        case 'h':
            fwrite(STDOUT,
                $_SERVER['PHP_SELF'] . ": Generate xml seed file for use with phpunit.\n
                Takes explicit table name and 1 or more explicit column names from that table, separated by commas.\n");
            $_SERVER['PHP_SELF'] . " -t<table> -c<columns>[,...] -o<order-by> [-w<where>]\n";
            exit(1);
        case 't':
            $tablename = $opts['t'];
            break;
        case 'c':
            $columns = explode(',', $opts['c']);
            break;
        case 'o':
            $orderby = $opts['o'];
            break;
        case 'w':
            $where = $opts['w'];
            break;
    }
}

$host     = MY_HOST;
$user     = MY_USER;
$pass     = MY_PASS;
$database = MY_DB;
if (!isset($tablename) || empty($tablename)) {
    echo "tablename must be set.  pass a -t option";
    die();
}
if (!isset($columns) || empty($columns)) {
    echo "columns must be set.  pass a -c option with the explicit list of columns to include";
    die();
}
if (!isset($orderby) || empty($orderby)) {
    echo "orderby must be set.  pass a -o option with the explicit name of an order-by column";
    die();
}

$xml_output = "<?xml version=\"1.0\"?>\n";
fwrite(STDOUT, "<!--specified table: " . $tablename . "-->\n");
fwrite(STDOUT, "<!--specified where: " . $where . "-->\n");
fwrite(STDOUT, "<!--specified orderby: " . $orderby . "-->\n");

$link = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $link) or die("Could not find database.");

$query = "SELECT " . implode(',',$columns) . " FROM $tablename";

// where clause
if (isset($where) && !empty($where)) {
    $query .= " WHERE $where";
}

if (isset($orderby) && !empty($orderby)) {
    $query .= " ORDER BY $orderby";
}

fwrite(STDOUT, $query . "\n");
$result = mysql_query($query, $link) or die("Data not found.");

$xml_output .= "<dataset>\n";
$xml_output .= "    <table name=\"$tablename\">\n";

// print the columns
foreach ($columns as $column) {
    $xml_output .= "        <column>$column</column>\n";
}

// the rows
while ($row = mysql_fetch_assoc($result)) {
    $xml_output .= "        <row>\n";
    foreach ($columns as $column) {
        $xml_output .= "            <value>{$row[$column]}</value>\n";
    }
    $xml_output .= "        </row>\n";
}

$xml_output .= "    </table>\n";
$xml_output .= "</dataset>";

echo $xml_output;
?>
