<?php
// The $_GET superglobal is used to determine which action should be taken.
if (isset($_GET['a'])) {

  // Include our DB credentials, create a connection, and sanitize_user_input 
  include_once('database.php');

  // switch method to call different functionality
  switch (strtolower($_GET['a'])) {
    // add - create a new ToDo item and save to the database
    case 'add':
      $item_text = sanitize_user_input($_POST['text'], $conn);
      // debug_info is being passed by reference
      $new_item = ToDo::createItem($item_text, $conn, $debug_info);
      // respond to the request with the json string 
      echo json_encode(array("item" => $new_item, "debug_info" => $debug_info));
      break;

    // update - update an existing ToDo item in the database
    case 'update':
      $text = sanitize_user_input($_POST['text'], $conn);
      // convert the id to an INT
      $id = intval($_POST['id']);
      // is_complete must be a boolean, so we have to conver the string
      $is_complete = $_POST['is_complete'] == 'true' ? true : false;
 
      // create a new item with the changed values
      $new_item = new ToDo($text, $id, $is_complete);
      $debug_info = $new_item->save($conn);
      // respond to the request with the json string 
      echo json_encode(array("item" => $new_item, "debug_info" => $debug_info));
      break;

    // list - get all the ToDo items and return them as json
    case 'list':
        $array_of_items = ToDo::getItems($conn);

        echo json_encode($array_of_items);
      break;
    // default - an invalid action as passed in $_GET['a']
    default:
      echo "Error: Invalid Action";
      break;
  }
  // Close the dabase connection when we are done.
  $conn->close();
} else {
  // no action was passed in $_GET['a']
  echo "Error: No Action";
}

/* class ToDo
 * -----------------------------------------------------------------------------
 * ToDo provides a model for a single ToDo item and methods to create and 
 * update individual ToDo items. It also provide a static helper method to 
 * get all of the ToDo items in the database and return them in an array.
 */
class Todo {
    public $id = 0;
    public $text = '';
    public $is_complete = false;

    /* This contructor takes advantage of PHP's ability to have an arbitrary 
     * number of arguments passed to a function. This is simulating method 
     * overloading. 
     * $a contains an array of the arguments passed to the method.
     * $n contains the number of arguments passed.
     *
     * switches "fall through" if you do not use a break statement. This means
     * that the subsequent cases are called. So, if we use 1 argument,  then 
     * only the code for case 1 is called. If we use 2 arguments, then we the 
     * code for case 2 is our entry point, then we fall through to case 1. 
     * Similarly, if the method is called with 3 arguments, the entry point is
     * case 3, then we fall through to case 2, and finally we fall through to 
     * case 1.
     */
    public function __construct() {
      $a = func_get_args();
      $n = func_num_args();
      switch($n) {
        case 3:
          $this->is_complete = $a[2];
        case 2:
          $this->id = $a[1];
        case 1:
          $this->text = $a[0];
        default:
          break;
      }
    }

    /* The method save takes a mysqli connection as its only argument. It first 
     * checks if the id of the object is greater than 0. If it is grearter than 
     * 0, then that means that it is updating an existing ToDo item. If it is 
     * equal to 0, then it is inserting a new ToDo item.
     */
    public function save($conn) {
      if ($this->id > 0) {
        return $this->update($conn);
      } else {
        return $this->insert($conn);
      }
    }

    /* The method insert is private (can only be called by other methods in the
     * ToDo class) and takes a mysqli connection as its only argument.
     * This method handles the inserting of a new row in the todo_items table
     * and updates the object with the new primary key value. 
     *
     * Returns: associative array containing mysql errors and other debug info
     */
    private function insert($conn) {
      // SQL query to insert a new row in the todo_items table.
      $sql = 'INSERT INTO todo_items (item_text) VALUES (?)';
      // Create a prepared statement using the SQL query.
      $stmt = $conn->prepare($sql);
      // Bind the property "text" as a string to the prepared statment.
      $stmt->bind_param("s", $this->text);

      /* Create an associative array to contain mysql errors and the last 
       * autoincrememnt id to help with debuging.
       * Use -1 to mean no errors.
       */
      $debug_info = array("mysql_errno" => -1, 
                          "mysql_error" => "", 
                          "insert_id" => 0);
      
      // Try to run the prepared statement
      if (!$stmt->execute()) {
        // if not successful, then save the errors to the debug info
        $debug_info["mysql_errno"] = $conn->errno;
        $debug_info["mysql_error"] = $conn->error;
      } else {
        // if successful, then get the last autoincremented primary key
        $this->id = $conn->insert_id;
        $debug_info["insert_id"] = $this->id;
      }

      // close the prepared statement
      $stmt->close();
      
      // Return our debug information
      return $debug_info;
    }

    /* The method update is private (can only be called by other methods in the
     * ToDo class) and takes a mysqli connection as its only argument.
     * This method handles the updating of a existing row in the todo_items 
     * table and updates the object with the changed data. 
     *
     * Returns: associative array containing mysql errors and other debug info
     */
    private function update($conn) {

      // SQL query to udpate an existing row in the todo_items table
$sql = <<<HERDOC
UPDATE todo_items 
   SET item_text = ?, 
       is_complete = ? 
 WHERE todo_id = ?
HERDOC;
      // Create a prepared statement using the SQL query.
      $stmt = $conn->prepare($sql);
      // Bind the property to the prepared statment.
      $stmt->bind_param("sii", $this->text, $this->is_complete, $this->id);
      
      /* Create an associative array to contain mysql errors and the number of
       * rows affected by the query.
       * Use -1 to mean no errors.
       */
      $debug_info = array("mysql_errno" => -1, 
                          "mysql_error" => "", 
                          "affected_rows" => 0);

      // Try to run the prepared statement
      if (!$stmt->execute()) {
        // if not successful, then save the errors to the debug info
        $debug_info["mysql_errno"] = $conn->errno;
        $debug_info["mysql_error"] = $conn->error;
      }
      // get the number of rows affected by the query
      $debug_info["affected_rows"] = $conn->affected_rows;

      // close the prepared statement
      $stmt->close();

      // Return our debug information
      return $debug_info;
    }


    /* static method to create a new item in the database
     *
     * Abstracts the instantiation of a new ToDo item (rather than an existing
     * one). 2 required arguments and 1 optional argument. 
     * $text is the new item's text
     * $conn is a mysqli connection
     * &$debug_info is passed by reference; it points to the variable where we 
     * want to store the values for debug_info returned by the method "save".
     *
     * Why? We don't have an id because this is a brand new item. If we 
     * manually create an item, then we could forget to call save, which means 
     * that it would never end up in the database. By using this static method
     * to create an item, we are safely creating an item and making sure that 
     * it is saved to the database.
     */
    public static function createItem($text, $conn, &$debug_info = null) {
      $new_item = new ToDo($text);
      $debug_info = $new_item->save($conn);
      return $new_item;
    }

    /* static method to get an array of all the ToDo items in the database
     *
     * Bundles the functionality of querying the database for the list of all
     * ToDo items and creating ToDo items from the returned results.
     * 
     * Takes 3 arguments: 1 required, 2 optional.
     * $conn is a mysqli connection
     * $offest is the offset for the sql limit (defaults to 0)
     * $count is the number of items to select (defaults to -1 which is all)
     */
    public static function getItems($conn, $offset = 0, $count = -1) {
      // SELECT query with LIMIT to support pagination
      $sql = 'SELECT * FROM todo_items LIMIT ?, ?';
      // Create a prepared statement using the SQL query.
      $stmt = $conn->prepare($sql);
      // Bind the properties to the prepared statment.
      $stmt->bind_param("ii", $offest, $count);
      
      // create an empty array to store the ToDo items in
      $array_of_items = array();

      // Try to run the prepared statement
      if ($stmt->execute()) {
        /* if it works, bind the results to the variables named $id, $text, and
         * $is_complete. This is a weirdness caused by using prepared 
         * statements. We have to bind the columns to variables in order to 
         * access them.
         */
        $stmt->bind_result($id, $text, $is_complete);
        // Loop through the results until we run out
        while($stmt->fetch()) {
          /* Create a new ToDo item using the values bound to the variables we 
           * named in the call to bind_result. Each call to fetch assigns the 
           * next rows values to those variables.
           */
          $item = new ToDo($text, $id, $is_complete);
          // push the new item onto the array_of_items.
          array_push($array_of_items, $item);
        }
        // good housekeeping: close our statement.
        $stmt->close();

      }
      // Return array of items. If no items, then it is empty.
      return $array_of_items;
    }
  }
 ?>
