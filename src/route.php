<?php 
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