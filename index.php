<?php
class ErrorMessage
{
    private static $instance = null;
    public $messages = array();

    public function __construct()
    {
        
    }


    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add($message)
    {
        $this->messages[] = $message;
    }

}
?><?php 

    function errorPage() {
        $template = "<!DOCTYPE html>\r\n<html lang=\"en\">\r\n\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Database Login</title>\r\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\r\n</head>\r\n\r\n<body>\r\n    <div class=\"container-fluid d-flex justify-content-center align-items-center vh-100 bg-danger\">\r\n        <div class=\"card shadow-lg p-4 d-flex align-items-center\" style=\"width: 400px;\">\r\n            <h4 class=\"text-center mb-4\">Error</h4>\r\n            <p>__ERROR_MESSAGE__</p>\r\n            <a href=\"index.php?action_logout=1\">Back to login</a>\r\n        </div>\r\n    </div>\r\n</body>\r\n\r\n</html>";
        $msg = "";

        foreach(ErrorMessage::get()->messages as $m) {
            $msg .= $m ."</br>";
        }
        $template = str_replace("__ERROR_MESSAGE__",$msg, $template);
        return $template;
    }

?><?php 

    class MYSQL {

        public $connection;
        public $schema;
        public $isInitialized = false;
        
        public function initialize() {
            $details = getDatabaseDetails();
    
            $servername = $details['server'];
            $port = $details['port'];
            $username = $details['user'];
            $password = $details['password'];

            if(!$this->schema){
                $this->schema = $details['schema'];
            }
            
            try {
                $dsn = "mysql:host=$servername:$port" ;
                $this->connection = new PDO($dsn, $username, $password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->isInitialized = true;
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getDatabases() {
            try {
                $stmt = $this->connection->query("SHOW DATABASES");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getTables() {
            if(!$this->schema){
                return array();
            }
            try {
                $this->connection->exec("USE ". $this->schema .";");
                $stmt = $this->connection->query("SHOW TABLES");
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function getAutocompleteKeywords() {
            try {
                $keywords = [];
                $stmt = $this->connection->query("
                    SELECT TABLE_NAME, COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE()
                ");
                
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
                foreach ($result as $row) {
                    $keywords[] = $row['TABLE_NAME'];
                    $keywords[] = $row['COLUMN_NAME'];
                }
        
                return array_unique($keywords);
            } catch (PDOException $e) {
                throw new Exception("". $e->getMessage());
            }
        }

        public function executeQuery($query) {
            try {
                $this->connection->exec("USE ". $this->schema .";");
                if (preg_match('/^\s*SELECT/i', $query) && !preg_match('/LIMIT\s+\d+/i', $query)) {
                    $query = rtrim($query, ";") . " LIMIT 100;";
                }

                $stmt = $this->connection->prepare($query);
                $stmt->execute();
    
                if (preg_match('/^\s*(INSERT|UPDATE|DELETE)/i', $query)) {
                    return ["affected_rows" => $stmt->rowCount()];
                } else {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

            } catch (PDOException $e) {
                return ["error" => $e->getMessage()];
            } catch (Exception $e) {
                return ["error" => $e->getMessage()];
            }
        }

    }


?><?php
class DBClient
{
    private static $instance = null;
    private $connection;

    public function __construct($schema = null)
    {
        $this->connection = new MYSQL();
        if ($schema) {
            $this->connection->schema = $schema;
        }
        if (!$this->connection->isInitialized) {
            $this->connection->initialize();
        }
    }


    public static function get($schema = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($schema);
        }
        return self::$instance->connection;
    }

}
?><?php

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
                                      <li><a class="dropdown-item" href="index.php?action_view_100_rows=1&table='. $c .'">View 100 Rows</a></li>
                                      <li><a class="dropdown-item" href="index.php?action_desc_table=1&table='. $c .'">Describe</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>';
    }
    return $out;
}



function getThemeCss() {
    $css = '';
    if(file_exists('theme.css')) {
        $css = file_get_contents('theme.css');
    }

    $theme = "body,\r\ninput,\r\ntextarea,\r\nselect,\r\nbutton,\r\nul,\r\nli {\r\n    font-size: 0.9rem;\r\n    font-family: monospace;\r\n}\r\n\r\nhr {\r\n    margin: 0.5rem 0;\r\n}\r\n\r\n.login-title {\r\n    background-color: var(--primary);\r\n    padding: 20px;\r\n    color: white;\r\n    font-weight: bold;\r\n}\r\n\r\n.login-form-container {\r\n    padding: 10px;\r\n}\r\n\r\n.login-form-container label {\r\n    font-weight: bold;\r\n    margin-bottom: 5px;\r\n}\r\n\r\n.login-button-container {\r\n    width: 100%;\r\n    display: flex;\r\n    flex-direction: row;\r\n    justify-content: end;\r\n}\r\n\r\n.login-button {\r\n    background-color: var(--primary);\r\n    border: none;\r\n\r\n}\r\n\r\n.login-button:hover {\r\n    background-color: var(--primary);\r\n}\r\n\r\n.dashboard-navigation-bar {\r\n    background-color: var(--primary);\r\n    height: 30px;\r\n    display: flex;\r\n    flex-direction: row;\r\n    flex-wrap: nowrap;\r\n    align-items: center;\r\n}\r\n\r\n.logo-container,\r\n.dashboard-schema-select-container {\r\n    flex-shrink: 1;\r\n}\r\n\r\n.logo-name {\r\n    color: white;\r\n    font-weight: bold;\r\n    padding: 0px;\r\n    font-size: 1.2rem;\r\n}\r\n\r\n.dashboard-schema-select {\r\n    display: flex;\r\n    flex-direction: row;\r\n    justify-content: end;\r\n\r\n}\r\n\r\n.dashboard-schema-select label {\r\n    font-weight: bold;\r\n    color: white;\r\n    padding-right: 10px;\r\n}\r\n\r\n.dashboard-schema-select select {\r\n    border-radius: 5px;\r\n    border: none;\r\n    padding-top: 0px;\r\n    padding-bottom: 0px;\r\n    height: 24px;\r\n    min-width: min-content;\r\n    font-size: 0.9rem;\r\n}\r\n\r\n\r\n.dashboard-table-row-container li {\r\n    font-size: 0.7rem;\r\n}\r\n\r\n.navigation-logout-button {\r\n    color: white;\r\n    padding-left: 10px;\r\n}\r\n\r\n\r\n.dashboard-inner-container {\r\n    height: calc(100% - 30px);\r\n    display: flex;\r\n    flex-direction: row;\r\n}\r\n\r\n.dashboard-sidebar-container {\r\n    height: 100%;\r\n    overflow: auto;\r\n    flex-grow: 1;\r\n    padding-top: 15px;\r\n    padding-right: 10px;;\r\n}\r\n\r\n.dashboard-view-container {\r\n    height: 100%;\r\n    display: flex;\r\n    flex-direction: column;\r\n    flex-grow: 1;\r\n    overflow: auto;\r\n}\r\n\r\n\r\n/* Query pane */\r\n\r\n.query-editor-container {\r\n    width: 100%;\r\n    flex-grow: 1;\r\n}\r\n\r\n.query-editor-inner-container {\r\n    width: 100%;\r\n    height: 100%;\r\n}\r\n\r\n.query-editor-form {\r\n    display: none;\r\n}\r\n\r\n\r\n.query-result-container {\r\n    width: 100%;\r\n    flex-grow: 1;\r\n    z-index: 999;\r\n    background-color: white;\r\n}\r\n\r\n.query-result-table-container {\r\n    overflow: auto;\r\n    width: 100%;\r\n    height: auto;\r\n}\r\n\r\n.query-editor-toolbar {\r\n    width: 100%;\r\n    background-color: #f7f7f7;\r\n    padding-left: 30px;\r\n    padding-top: 5px;\r\n    padding-bottom: 7px;\r\n    font-size: 0.7rem !important;\r\n}\r\n\r\n.query-editor-run-button {\r\n    font-size: 0.7rem !important;\r\n}\r\n\r\n.query-result-toolbar {\r\n    height: 0px;\r\n    background-color: black;\r\n    width: 100%;\r\n}\r\n\r\n.query-result-table {\r\n    font-size: 0.7rem;\r\n    width: auto;\r\n    border-collapse: collapse;\r\n\r\n}\r\n\r\n.query-result-table th,\r\n.query-result-table td {\r\n    padding: 0.2rem;\r\n}\r\n\r\n.query-result-table th {\r\n    font-weight: bold;\r\n    background-color: var(--primary);\r\n    color: white;\r\n}\r\n\r\n.query-result-table .number-cell {\r\n    font-weight: bold;\r\n    background-color: var(--primary);\r\n    color: white;\r\n    min-width: 25px;\r\n}\r\n\r\n\r\n.query-result-table td,\r\n.query-result-table th {\r\n    max-width: 500px;\r\n    white-space: nowrap;\r\n    /* Prevent text wrapping */\r\n    overflow: hidden;\r\n    text-overflow: ellipsis;\r\n    /* Show \"...\" for overflow */\r\n}\r\n\r\n.query-result-message-error {\r\n    color: red;\r\n}\r\n\r\n.query-result-message {\r\n    padding: 10px;\r\n}\r\n\r\n\r\n.split {\r\n    display: flex;\r\n    flex-direction: row;\r\n}\r\n\r\n.gutter {\r\n    background-color: #eee;\r\n    background-repeat: no-repeat;\r\n    background-position: 50%;\r\n    z-index: 999;\r\n}\r\n\r\n.gutter.gutter-horizontal {\r\n    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAeCAYAAADkftS9AAAAIklEQVQoU2M4c+bMfxAGAgYYmwGrIIiDjrELjpo5aiZeMwF+yNnOs5KSvgAAAABJRU5ErkJggg==');\r\n    cursor: col-resize;\r\n    z-index: 999;\r\n\r\n}\r\n\r\n.gutter.gutter-vertical {\r\n    background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAFAQMAAABo7865AAAABlBMVEVHcEzMzMzyAv2sAAAAAXRSTlMAQObYZgAAABBJREFUeF5jOAMEEAIEEFwAn3kMwcB6I2AAAAAASUVORK5CYII=');\r\n    cursor: row-resize;\r\n    z-index: 999;\r\n\r\n}\r\n\r\n\r\n.CodeMirror * {\r\n    font-family: monospace;\r\n    font-size: 13px;\r\n}\r\n\r\n:root {\r\n    --primary: #2196F3;\r\n}\r\n  ";
    return $theme . $css;
}

function getDashboardHtml() {
    $template = "<!DOCTYPE html>\r\n<html lang=\"en\">\r\n\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Database Login</title>\r\n\r\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\r\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.css\">\r\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.css\">\r\n\r\n    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/split.js/1.6.0/split.min.js\"></script>\r\n    <script src=\"https://code.jquery.com/jquery-3.7.1.min.js\"></script>\r\n    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>\r\n\r\n    __THEME_CSS__\r\n\r\n\r\n</head>\r\n\r\n<body>\r\n    <div class=\"container-fluid vh-100 dashboard-main-container\">\r\n        <div class=\"row dashboard-navigation-bar\">\r\n            <div class=\"logo-container\">\r\n                <label class=\"logo-name\">AdminX <span style=\"font-size: 11px;\">v0.1.1</span></label>\r\n            </div>\r\n            <div class=\"dashboard-schema-select-container\">\r\n                <div class=\"dashboard-schema-select\">\r\n                    <label>Schema </label>\r\n                    <select class=\"form-select w-auto dashboard-schema-select-form\"\" aria-label=\" Small select example\">\r\n                        __DB_OPTIONS__\r\n                    </select>\r\n                    <a href=\"index.php?action_logout=1\" class=\"navigation-logout-button\">Logout</a>\r\n                </div>\r\n            </div>\r\n        </div>\r\n        <div class=\"dashboard-inner-container\">\r\n            <div class=\"dashboard-sidebar-container\" id=\"dashboardSidebarContainer\">\r\n                <div class=\"dashboard-tables-container\">\r\n\r\n                    <!-- <select class=\"form-select form-select-sm\" aria-label=\"Small select example\">\r\n                        <option>Select database</option>\r\n                        __DB_OPTIONS__\r\n                    </select>\r\n                    <hr> -->\r\n                    <input class=\"form-control form-control-sm dashboard-search-tables-input\" type=\"text\"\r\n                        placeholder=\"Search table\">\r\n                    <table class=\"table mt-1 dashboard-tables-list\">\r\n                        __TABLES__\r\n                    </table>\r\n                </div>\r\n                <div class=\"dashboard-tables-container-not-table\" style=\"display: none;\">\r\n                    \r\n                    <div class=\"alert alert-warning text-center\" role=\"alert\">\r\n                        Select schema to view tables\r\n                      </div>\r\n                </div>\r\n            </div>\r\n            <div class=\"dashboard-view-container\" id=\"dashboardViewContainer\">\r\n                __MAIN_CONTENT__\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n\r\n\r\n\r\n    <script>\r\n        Split(['#dashboardSidebarContainer', '#dashboardViewContainer'], {\r\n            sizes: [20, 80],\r\n            onDragEnd: ($('#queryEditor').length) ? function (sizes) {\r\n                setEditorSize();\r\n            } : function (size) { },\r\n            onDrag: ($('#queryEditor').length) ? function (sizes) {\r\n                setEditorSize();\r\n            } : function (size) { }\r\n        });\r\n\r\n        $(\".dashboard-search-tables-input\").keyup(function () {\r\n            var value = this.value;\r\n            $(\".dashboard-table-row\").each(function (index) {\r\n                var id = $(this).find(\"td\").find('a').data('table-name');\r\n                console.log(id.indexOf(value) !== -1, id, value);\r\n                $(this).toggle(id.indexOf(value) !== -1);\r\n            });\r\n        });\r\n\r\n        function changeScema(schema) {\r\n\r\n        }\r\n        $(document).ready(function () {\r\n            $('.dashboard-schema-select-form').change(function () {\r\n                var selectedValue = $(this).val();\r\n                window.location.href = 'index.php?action_change_schema=1&schema=' + selectedValue;\r\n            });\r\n            if($('.dashboard-schema-select-form').val().length == 0) {\r\n                $('.dashboard-tables-container-not-table').show();\r\n                $('.dashboard-tables-container').hide();\r\n            }\r\n        });\r\n\r\n    </script>\r\n\r\n</body>\r\n</body>\r\n\r\n</html>";
    $template = str_replace('__DB_OPTIONS__', getDatabasesOptions(), $template);
    $template = str_replace('__TABLES__', getTablesRow(), $template);
    $template = str_replace('__THEME_CSS__', '<style>' . getThemeCss() . '</style>', $template);
    return $template;
}

?><?php 

    function isDatabaseDetailsAvailable() {
        if(
            isset($_COOKIE['server']) 
            && isset($_COOKIE['port']) 
            && isset($_COOKIE['user']) 
        ) {
            return true;
        }
        return false;
    }

    function isDatabaseDetailsAvailableInPost() {
        if(
            isset($_POST['server']) 
            && isset($_POST['port']) 
            && isset($_POST['user']) 
        ) {
            return true;
        }
        return false;
    }

    function setDatabaseDetails() {
        if(!isDatabaseDetailsAvailableInPost()) {
            return false;
        }
        setcookie('server', $_POST['server']);
        setcookie('port', $_POST['port']);
        setcookie('user', $_POST['user']);
        setcookie('password', isset($_POST['password']) ? $_POST['password'] : "");
        setcookie('schema', isset($_POST['schema']) ? $_POST['schema'] : null);
        return true;
    }

    function clearDatabaseDetails() {
        setcookie('server', null);
        setcookie('port', null);
        setcookie('user', null);
        setcookie('password', null);
        setcookie('schema', null);
    }

    function setSchema() {
        setcookie('schema', isset($_POST['schema']) ? $_POST['schema'] : null);
    }

    function getDatabaseDetails() {
        if(isDatabaseDetailsAvailableInPost()) {
            return [
                'server'   => $_POST['server'] ?? null,
                'port'     => $_POST['port'] ?? null,
                'user'     => $_POST['user'] ?? null,
                'password' => $_POST['password'] ?? "",
                'schema' => $_POST['schema'] ?? null,
            ];
        }
        return [
            'server'   => $_COOKIE['server'] ?? null,
            'port'     => $_COOKIE['port'] ?? null,
            'user'     => $_COOKIE['user'] ?? null,
            'password' => $_COOKIE['password'] ?? "",
            'schema' => $_COOKIE['schema'] ?? null,
        ];
    }
    
?><?php 

    function loginPage() {
        $template = "<!DOCTYPE html>\r\n<html lang=\"en\">\r\n\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Database Login</title>\r\n    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\r\n    __THEME_CSS__\r\n</head>\r\n\r\n<body>\r\n    <div class=\"container d-flex justify-content-center align-items-center vh-100\">\r\n        <div class=\"card\" style=\"width: 400px;\">\r\n            <h4 class=\"text-center login-title\" >AdminX</h4>\r\n            <div class=\"login-form-container\">\r\n                <form action=\"index.php\" method=\"POST\">\r\n                    <div class=\"mb-1\">\r\n                        <label class=\"form-label\">Host</label>\r\n                        <input type=\"text\" class=\"form-control\" placeholder=\"Enter server\" name=\"server\" required />\r\n                    </div>\r\n                    <div class=\"mb-1\">\r\n                        <label class=\"form-label\">Port</label>\r\n                        <input type=\"number\" class=\"form-control\" placeholder=\"Enter port\" name=\"port\" required />\r\n                    </div>\r\n                    <div class=\"mb-1\">\r\n                        <label class=\"form-label\">Username</label>\r\n                        <input type=\"text\" class=\"form-control\" placeholder=\"Enter username\" name=\"user\" required />\r\n                    </div>\r\n                    <div class=\"mb-1\">\r\n                        <label class=\"form-label\">Password</label>\r\n                        <input type=\"password\" class=\"form-control\" placeholder=\"Enter password\" name=\"password\" />\r\n                    </div>\r\n                    <div class=\"mb-3\">\r\n                        <label class=\"form-label\">Database</label>\r\n                        <input type=\"text\" class=\"form-control\" placeholder=\"Enter database name\" name=\"schema\" />\r\n                    </div>\r\n                    <div class=\"login-button-container\">\r\n                        <input class=\"btn btn-primary login-button\" name=\"action_connect\" value=\"Connect\" type=\"submit\" />\r\n                    </div>\r\n                </form>\r\n            </div>\r\n\r\n        </div>\r\n    </div>\r\n</body>\r\n\r\n</html>";
        $template = str_replace('__THEME_CSS__', '<style>' . getThemeCss() . '</style>', $template);
        return $template;
    }

?><?php



function queryPage()
{
    $template = getDashboardHtml();
    $queryPanelHtml = "<div class=\"query-editor-container\" id=\"queryEditorContainer\">\r\n    <div class=\"query-editor-toolbar\">\r\n        <button type=\"button\" class=\"btn btn-success btn-sm query-editor-run-button\" onclick=\"submitQueryForm()\">\r\n            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-play\"\r\n                viewBox=\"0 0 16 16\">\r\n                <path\r\n                    d=\"M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z\" />\r\n            </svg>\r\n            Run\r\n        </button>\r\n    </div>\r\n    <div id=\"queryEditor\" class=\"query-editor-inner-container\">\r\n        <textarea id=\"queryEditorTextArea\"></textarea>\r\n    </div>\r\n\r\n    <form action=\"\" class=\"query-editor-form\" method=\"POST\">\r\n        <textarea id=\"form-input-query\" name=\"query\"></textarea>\r\n        <input type=\"text\" name=\"action_execute_query\" value=\"submit\" />\r\n    </form>\r\n</div>\r\n<div class=\"query-result-container\" id=\"queryResultContainer\">\r\n   <div class=\"query-result-toolbar\">\r\n\r\n   </div>\r\n\r\n   <div class=\"query-result-table-container\">\r\n        __RESULT__\r\n   </div>\r\n</div>\r\n\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/sql/sql.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/sql-hint.min.js\"></script>\r\n\r\n\r\n<script>\r\n    var customKeywords = __EDITOR_KEYWORDS__;\r\n    var executedQuery = \"__QUERY__\";\r\n\r\n    function sqlHint(editor) {\r\n        var cursor = editor.getCursor();\r\n        var token = editor.getTokenAt(cursor);\r\n        var suggestions = [];\r\n\r\n        var sqlKeywords = [\"SELECT\", \"FROM\", \"WHERE\", \"INSERT\", \"UPDATE\", \"DELETE\", \"JOIN\", \"ON\", \"ORDER BY\", \"GROUP BY\", \"HAVING\", \"LIMIT\"];\r\n        \r\n        suggestions = suggestions.concat(sqlKeywords);\r\n        suggestions = suggestions.concat(customKeywords);\r\n\r\n        return {\r\n        list: suggestions.filter(s => s.toLowerCase().includes(token.string.toLowerCase())), // Filter by input\r\n        from: { line: cursor.line, ch: token.start },\r\n        to: { line: cursor.line, ch: token.end }\r\n    };\r\n    }\r\n\r\n\r\n    var editor = CodeMirror.fromTextArea(document.getElementById(\"queryEditorTextArea\"), {\r\n        mode: \"text/x-sql\",\r\n        lineNumbers: true,\r\n        theme: \"default\",\r\n        autoCloseBrackets: true,\r\n        extraKeys: {\r\n            \"Ctrl-Space\": function(cm) {\r\n                cm.showHint({ hint: sqlHint, completeSingle: false });\r\n            },\r\n            \"Tab\": function(cm) {\r\n                if (cm.somethingSelected()) {\r\n                    cm.indentSelection(\"add\");\r\n                } else {\r\n                    cm.showHint({ hint: sqlHint, completeSingle: false });\r\n                }\r\n            }\r\n        },\r\n    });\r\n\r\n    editor.on(\"inputRead\", function(cm, event) {\r\n        if (!cm.state.completionActive && event.origin !== \"paste\") {\r\n            cm.showHint({ hint: sqlHint, completeSingle: false });\r\n        }\r\n    });\r\n\r\n\r\n    function setEditorSize() {\r\n        editor.setSize($('#queryEditor').width(), $('#queryEditor').height() - ($('.query-editor-toolbar').height() + 10));\r\n        $('.query-result-table-container').width($('#queryResultContainer').width()).height($('#queryResultContainer').height() - $('.query-result-toolbar').height());\r\n    }\r\n\r\n    setTimeout(() => {\r\n        setEditorSize();\r\n        \r\n        if(!executedQuery.startsWith(\"__QUERY\")) {\r\n            editor.setValue(executedQuery);\r\n        }\r\n    }, 100);\r\n\r\n    Split(['#queryEditorContainer', '#queryResultContainer'], {\r\n        sizes: [40, 60],\r\n        direction: 'vertical',\r\n        onDragEnd: function (sizes) {\r\n            setEditorSize();\r\n        },\r\n        onDrag: function (sizes) {\r\n            setEditorSize();\r\n        }\r\n    });\r\n\r\n    $(window).on(\"resize\", function () {\r\n        setEditorSize();\r\n    });\r\n\r\n\r\n    function submitQueryForm() {\r\n        $('#form-input-query').val(editor.getValue());\r\n        $('.query-editor-form').submit();\r\n    }\r\n\r\n   \r\n\r\n</script>";
    $keywords = DBClient::get()->getAutocompleteKeywords();
    $template = str_replace("__MAIN_CONTENT__", $queryPanelHtml, $template);
    $template = str_replace("__EDITOR_KEYWORDS__", json_encode(array_values($keywords)), $template);
    $template = str_replace("__RESULT__", '', $template);
    return $template;
}

function queryResultPage($query)
{
    $template = getDashboardHtml();
    $queryPanelHtml = "<div class=\"query-editor-container\" id=\"queryEditorContainer\">\r\n    <div class=\"query-editor-toolbar\">\r\n        <button type=\"button\" class=\"btn btn-success btn-sm query-editor-run-button\" onclick=\"submitQueryForm()\">\r\n            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-play\"\r\n                viewBox=\"0 0 16 16\">\r\n                <path\r\n                    d=\"M10.804 8 5 4.633v6.734zm.792-.696a.802.802 0 0 1 0 1.392l-6.363 3.692C4.713 12.69 4 12.345 4 11.692V4.308c0-.653.713-.998 1.233-.696z\" />\r\n            </svg>\r\n            Run\r\n        </button>\r\n    </div>\r\n    <div id=\"queryEditor\" class=\"query-editor-inner-container\">\r\n        <textarea id=\"queryEditorTextArea\"></textarea>\r\n    </div>\r\n\r\n    <form action=\"\" class=\"query-editor-form\" method=\"POST\">\r\n        <textarea id=\"form-input-query\" name=\"query\"></textarea>\r\n        <input type=\"text\" name=\"action_execute_query\" value=\"submit\" />\r\n    </form>\r\n</div>\r\n<div class=\"query-result-container\" id=\"queryResultContainer\">\r\n   <div class=\"query-result-toolbar\">\r\n\r\n   </div>\r\n\r\n   <div class=\"query-result-table-container\">\r\n        __RESULT__\r\n   </div>\r\n</div>\r\n\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/sql/sql.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/show-hint.min.js\"></script>\r\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/addon/hint/sql-hint.min.js\"></script>\r\n\r\n\r\n<script>\r\n    var customKeywords = __EDITOR_KEYWORDS__;\r\n    var executedQuery = \"__QUERY__\";\r\n\r\n    function sqlHint(editor) {\r\n        var cursor = editor.getCursor();\r\n        var token = editor.getTokenAt(cursor);\r\n        var suggestions = [];\r\n\r\n        var sqlKeywords = [\"SELECT\", \"FROM\", \"WHERE\", \"INSERT\", \"UPDATE\", \"DELETE\", \"JOIN\", \"ON\", \"ORDER BY\", \"GROUP BY\", \"HAVING\", \"LIMIT\"];\r\n        \r\n        suggestions = suggestions.concat(sqlKeywords);\r\n        suggestions = suggestions.concat(customKeywords);\r\n\r\n        return {\r\n        list: suggestions.filter(s => s.toLowerCase().includes(token.string.toLowerCase())), // Filter by input\r\n        from: { line: cursor.line, ch: token.start },\r\n        to: { line: cursor.line, ch: token.end }\r\n    };\r\n    }\r\n\r\n\r\n    var editor = CodeMirror.fromTextArea(document.getElementById(\"queryEditorTextArea\"), {\r\n        mode: \"text/x-sql\",\r\n        lineNumbers: true,\r\n        theme: \"default\",\r\n        autoCloseBrackets: true,\r\n        extraKeys: {\r\n            \"Ctrl-Space\": function(cm) {\r\n                cm.showHint({ hint: sqlHint, completeSingle: false });\r\n            },\r\n            \"Tab\": function(cm) {\r\n                if (cm.somethingSelected()) {\r\n                    cm.indentSelection(\"add\");\r\n                } else {\r\n                    cm.showHint({ hint: sqlHint, completeSingle: false });\r\n                }\r\n            }\r\n        },\r\n    });\r\n\r\n    editor.on(\"inputRead\", function(cm, event) {\r\n        if (!cm.state.completionActive && event.origin !== \"paste\") {\r\n            cm.showHint({ hint: sqlHint, completeSingle: false });\r\n        }\r\n    });\r\n\r\n\r\n    function setEditorSize() {\r\n        editor.setSize($('#queryEditor').width(), $('#queryEditor').height() - ($('.query-editor-toolbar').height() + 10));\r\n        $('.query-result-table-container').width($('#queryResultContainer').width()).height($('#queryResultContainer').height() - $('.query-result-toolbar').height());\r\n    }\r\n\r\n    setTimeout(() => {\r\n        setEditorSize();\r\n        \r\n        if(!executedQuery.startsWith(\"__QUERY\")) {\r\n            editor.setValue(executedQuery);\r\n        }\r\n    }, 100);\r\n\r\n    Split(['#queryEditorContainer', '#queryResultContainer'], {\r\n        sizes: [40, 60],\r\n        direction: 'vertical',\r\n        onDragEnd: function (sizes) {\r\n            setEditorSize();\r\n        },\r\n        onDrag: function (sizes) {\r\n            setEditorSize();\r\n        }\r\n    });\r\n\r\n    $(window).on(\"resize\", function () {\r\n        setEditorSize();\r\n    });\r\n\r\n\r\n    function submitQueryForm() {\r\n        $('#form-input-query').val(editor.getValue());\r\n        $('.query-editor-form').submit();\r\n    }\r\n\r\n   \r\n\r\n</script>";
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


?><?php 
function handleRoute() {

    // Login page
    if(isset($_GET['p']) && $_GET['p'] == 'login') {
        return loginPage();
    }


    // Connect to database with post data if failed redirect
    if(isset($_POST['action_connect'])) {
        try {
            DBClient::get();
            setDatabaseDetails();
            return '<script>window.location.href = "index.php";</script>';
        }catch(Exception $e) {
            ErrorMessage::get()->add($e->getMessage());
            return loginPage();
        }
    }

    // If connection details not available redirect to login page
    if(!isDatabaseDetailsAvailable()) {
        return '<script>window.location.href = "index.php?p=login";</script>';
    }
    

    // chcnage schema
    if(isset($_GET['action_change_schema'])) {
        try {
            DBClient::get()->schema = isset($_GET['schema']) ? $_GET['schema'] : null;
            setcookie('schema', isset($_GET['schema']) ? $_GET['schema'] : null);
        }catch(Exception $e) {
            ErrorMessage::get()->add($e->getMessage());
            return loginPage();
        }
    }


    try {
        if(isset($_POST['action_execute_query']) && isset($_POST['query'])) {
            return queryResultPage($_POST['query']);
        }
        if(isset($_GET['action_view_100_rows'])  && isset($_GET['table'])) {
            return viewTableRowsPage($_GET['table']);
        }
        if(isset($_GET['action_desc_table'])  && isset($_GET['table'])) {
            return describeTablePage($_GET['table']);
        }
    }catch(Exception $e) {
        ErrorMessage::get()->add($e->getMessage());
        return errorPage();
    }
    
    
    if(isset($_GET['action_logout'])) {
        clearDatabaseDetails();
        return '<script>window.location.href = "index.php?p=login";</script>';
    }
    return queryPage();
}
?>
<?php echo handleRoute(); ?>