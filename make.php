<?php

$data = file_get_contents('source.html');

$trDelim = <<<'EOT'
<tr class="v-grid-row
EOT;
$trDelimEnd = <<<'EOT'
</tr>
EOT;

$phpStorm = "\n";
/** @noinspection SqlResolve */
$startSqlLine = 'INSERT INTO countries (`code`, `iso3`, `name`) VALUES '.$phpStorm;
$repeatingValues = "('{{td2}}','{{td3}}','{{td0}}')";

$lines = explode($trDelim, $data);
array_shift($lines);

$trLines = [];
foreach ($lines as $line) {
    $trLines[] = explode($trDelimEnd, $line, 2)[0];
}

$ret = [];
foreach ($trLines as $trLine) {

    if (strpos($trLine, 'role="rowheader"') !== false) {
        continue;
    }
    $tdDatas = explode('<td', $trLine);
    array_shift($tdDatas);
    $cells = [];
    foreach ($tdDatas as $tdData) {
        $parts = explode('</td>', '<fake '.$tdData, 2);
        $cells[] = trim(strip_tags($parts[0]));
    }
    $ret[] = $cells;
}

$finalSql = $startSqlLine;
foreach ($ret as $values) {

    $searches = array_map(static function ($k) {
        return '{{td'.$k.'}}';
    }, array_keys($values));

    $replaces = array_map(static function ($v) {
        return addslashes($v);
    }, $values);

    $add = str_replace($searches, $replaces, $repeatingValues).',';
    $add = str_replace("\n", '', $add);
    $finalSql .= $add."\n";

}

echo substr(trim($finalSql), 0, -1);
echo "\n";


