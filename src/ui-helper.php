<?php

function getDatabasesOptions() {
        $db = DBClient::get();
        $schemas = $db->getDatabases();
        $out = "";
        $out .= '<option value="">Select Schema</option> \n';

        foreach($schemas as $c) {
           if($db->schema == $c) {
            $out .= '<option value="'.$c.'" selected>'. $c .'</option> \n';
           }else{
            $out .= '<option value="'.$c.'">'. $c .'</option> \n';
           }
        }
        return $out;
}

function getTablesRow() {
    $db = DBClient::get();
    $vals = $db->getTables();
    $out = "";
    foreach($vals as $c) {
       $out .= '         <tr class="dashboard-table-row">
                            <td>
                                <div class="dropdown dashboard-table-row-container">
                                    <a type="button" class="dropdown-toggle dashboard-table-row-item" data-bs-toggle="dropdown" data-table-name="'.$c.'" style="text-decoration: none; color: black;">
                                    <img style="width: 15px" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAkElEQVR4nO3WQQqAMAxE0dz/WBbPFXcuxCGVFJra/yA7kYRMIWYAvjrNzItX6xnEF6lQ94edpv3PGeQdGxGIVhbREohWFtES9o2WF6/Q7Aado/Hvb2QUBhHYSBbREohWFtESiNby0fLiFZrdoHP9PnA0Vn3sozCIwEayiJZAtLKIlrDfrdUKNOlBHT2DALDbBX2sGRStwMkLAAAAAElFTkSuQmCC" alt="table-1">
                                        '. $c .'
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                     <h6 class="dropdown-header">'. $c .' table</h6>
                                      <li><a class="dropdown-item" href="/index.php?action_view_100_rows=1&table='. $c .'">View 100 Rows</a></li>
                                      <li><a class="dropdown-item" href="/index.php?action_desc_table=1&table='. $c .'">Describe</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>';
    }
    return $out;
}



function getThemeCss() {
    $theme = "#FSTRINCLUDE assets/theme.css";
    return $theme;
}

function getDashboardHtml() {
    $template = "#FSTRINCLUDE assets/dashboard.html";
    $template = str_replace('__DB_OPTIONS__', getDatabasesOptions(), $template);
    $template = str_replace('__TABLES__', getTablesRow(), $template);
    $template = str_replace('__THEME_CSS__', '<style>' . getThemeCss() . '</style>', $template);
    return $template;
}

?>