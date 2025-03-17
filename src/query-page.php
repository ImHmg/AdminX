<?php



function queryPage()
{
    $template = getDashboardHtml();
    $queryPanelHtml = "#FSTRINCLUDE assets/query-pane.html";
    $keywords = DBClient::get()->getAutocompleteKeywords();
    $template = str_replace("__MAIN_CONTENT__", $queryPanelHtml, $template);
    $template = str_replace("__EDITOR_KEYWORDS__", json_encode(array_values($keywords)), $template);
    $template = str_replace("__RESULT__", '', $template);
    return $template;
}

function queryResultPage($query)
{
    $template = getDashboardHtml();
    $queryPanelHtml = "#FSTRINCLUDE assets/query-pane.html";
    $keywords = DBClient::get()->getAutocompleteKeywords();
    $template = str_replace("__MAIN_CONTENT__", $queryPanelHtml, $template);
    $template = str_replace("__EDITOR_KEYWORDS__", json_encode(array_values($keywords)), $template);
    $result = DBClient::get()->executeQuery($query);
    if (isset($result['error'])) {
        $message = '<label class="query-result-message query-result-message-error"> <b>[Error]</b> ' . $result['error'] . '</label>';
        $template = str_replace("__RESULT__", $message, $template);
    } else if (isset($result['affected_rows'])) {
        $message = '<label class="query-result-message query-result-message-success">Affected row count ' . $result['affected_rows'] . '</label>';
        $template = str_replace("__RESULT__", $message, $template);
    } else {
        $head = '<table class="table table-bordered query-result-table"><tr><th>#</th>';
        foreach (array_keys($result[0]) as $column) {
            $head .= "<th>" . htmlspecialchars($column ? $column : 'null') . "</th>";
        }
        $head .= "</tr>";
        $rows = "";
        $rowNumber = 1;
        foreach ($result as $row) {
            $rows .= "<tr>";
            $rows .= "<tr><td class=\"number-cell\">" . $rowNumber++ . "</td>";
            foreach ($row as $value) {
                $rows .= "<td>" . htmlspecialchars($value ? $value : 'null') . "</td>";
            }
            $rows .= "</tr>";
        }
        $rows .= '</table>';
        $template = str_replace("__RESULT__", $head . $rows, $template);
    }
    $template = str_replace("__QUERY__", $query, $template);
    return $template;

}

function viewTableRowsPage($table) {
    $query = 'SELECT * FROM '. $table .' LIMIT 100;';
    return queryResultPage($query);
}   

function describeTablePage($table) {
    $query = 'DESC '. $table .';';
    return queryResultPage($query);
}   


?>