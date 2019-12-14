/* function: createItem 
 * -------------------------------------------------------------------------
 * This function takes in an data object representing a ToDo item
 * and returns an html DOM object that represents a list item that
 * contains a checkbox and a span containing the item text. 
 * -------------------------------------------------------------------------
 * INPUT
 * The function takes a single object as an argument. The object 
 * represents the ToDo item and contains the following properties.
 * (It is basically an associative array.)
 * KEYS         | DESCRIPTION OF VALUES
 * -------------------------------------------------------------------------
 * id           | int - primary key
 * test         | string - todo item text
 * is_complete  | boolean - true means item is complete
 * -------------------------------------------------------------------------
 * OUTPUT
 * The returned jQuery object should match the template example that 
 * is in the index.html file. Shown below:
 * <li id="item_tmpl" class="template">
 *   <input type="checkbox" id="item_checkbox_tmpl" value="0"/>
 *   <span id="item_text">Item Text<span>
 * </li>
 * 
 * The id values need to have the "tmpl" replaced with the id of the item. 
 *    - For example: "item_1" and "item_checkbox_1"
 * The class "template" needs to be removed or the item won't be diplayed. 
 * The value for the checkbox needs to be replaced with id for the item. 
 * The text within the span needs to replaced with the ToDo item's text.
 * The checkbox needs to have the function bound to the "change" 
 * event, the event listener 
 */
function createItem(item) {
  // debug message
  console.log("createItem", item);
  let id = item["id"];
  let text = item["text"];
  let is_complete = item["is_complete"];

  // Create a copy of the template to use to create HTML for the new item.
  let new_item = $("#item_tmpl").clone(true);

  // TODO: Remove the "template" class from new_item
  new_item.removeClass("template");

  // TODO: When is_complete is true, add the class "completed"
  if (is_complete == true ) {
    new_item.addClass("completed");
  }

  // TODO: Change the id to "item_" + id
  new_item.attr("id", "item_" + id);

  // TODO: Change the text in the span with the id "item_text" 
  // to the value contained in "text"
  new_item.find("#item_text").text(text);

  // TODO: Set the value of the checkbox to the value in id
  new_item.find("input").val(id);
  // TODO: Change the id of the checkbox to "item_checkbox_" + id
  new_item.find("input").attr("id","item_checkbox_"+id)
  // TODO: When is_complete is true, make the checkbox be checked.
  if(is_complete == true){
    new_item.find("#item_checkbox_" + id).prop("checked", true);
  }
  // TODO: Bind the itemCheckboxListener to the checkbox's change event.
  new_item.find("input").on("change",itemCheckboxListener);

  
  // return the new ToDo item's html that was just prepared.
  return new_item;
}

/* function itemCheckboxListener
 * -------------------------------------------------------------------------
 * This is an event listener for the individual todo list item's 
 * checkbox. This event listener should be bound to the "change"
 * event for the input checkbox. 
 * -------------------------------------------------------------------------
 * INPUT
 * event - the event object that is passed to all event listeners.
 * -------------------------------------------------------------------------
 * OUTPUT
 * None.
 * -------------------------------------------------------------------------
 * FUNCTIONALITY
 * When this event is triggered, it should create an AJAX POST 
 * call to the url "engine.php?a=update". The server will be 
 * expecting 3 POST keys: "id", "text", "is_complete". This data is 
 * NOT sent using JSON. 
 * 
 * The response to the AJAX request WILL be in JSON. The response
 * is an object that contains 2 properties: "item" and "debug_info".
 * The item property contains the data about the updated item.
 * The debug_info property contains the mysql error messages and
 * the number of rows affected by the update query.
 * 
 */
function itemCheckboxListener(event) {
  // HINT: '$(this)' is the checkbox that triggered the event 
  // TODO: Use jQuery to save the value of the checkbox as the 'id'
  let id = $(this).val();
  // TODO: Use jQuery to save the html of the span with the id item_text in 'text'
  let text = $(this).parent().find("span#item_text").html();
  // TODO: Use jQuery to save whether the checkbox is checked or not
  let is_complete = $(this).prop('checked');

  // Creating the object to send to the server
  let data = {id: id,
              text: text,
              is_complete: is_complete};

  // debug message, does the object contain all the right values?
  console.log("itemCheckBoxListener", data);

  /* TODO: Create an AJAX call to POST data to the target url:
   *       engine.php?a=update
   * TODO: When the response comes back, add or emove the CSS class
   *       "completed" depending on the value of 'is_complete'.
   * NOTE: 
   * Remeber that the JSON response contains item and debug_info.
   */
 
  $.post("engine.php?a=update", data,function(response){
    let json = JSON.parse(response);
    
    (json.item.is_complete ? $("li#item_"+json.item.id).addClass("completed"):
      $("li#item_"+json.item.id).removeClass("completed"))
  });
}

/* function addClickListener
 * -------------------------------------------------------------------------
 * This is the event listener for the click event on the submit button. 
 * Sends an AJAX request to the server and creates a new ToDo item on the 
 * page if the request is succssfull.
 * -------------------------------------------------------------------------
 * INPUT
 * event - the event object that is passed to all event listeners.
 * -------------------------------------------------------------------------
 * OUTPUT
 * None.
 * -------------------------------------------------------------------------
 * FUNCTIONALITY
 * When this event is triggered, it should create an AJAX POST 
 * call to the url "engine.php?a=add". The server will be 
 * expecting 1 POST key: "text". This data is NOT sent using JSON. 
 * 
 * The response to the AJAX request WILL be in JSON. The response
 * is an object that contains 2 properties: "item" and "debug_info".
 * The item property contains the data about the updated item.
 * The debug_info property contains the mysql error messages and
 * the last autoincrement id from the INSERT query.
 */
function addClickListener(event) {
  // Prevents the button from causing the form to submit...
  event.preventDefault();
  // Debug message
  console.log('addClickListener');

  /* TODO: Create an ajax call to POST data to the target url
   *       "engine.php?a=add".
   *       The server expects a single POST key named "text".
   * TODO: If the call is successful, set the value of the text to "".
   * TODO: Use the function createItem by passing the returned JSON object
   *       and append the HTML object returned to the ul with the id 
   *       "todo_list".
   * NOTE: 
   * Remeber that the JSON response contains item and debug_info.
   */
   
  let text = $("#item_input").val();
  let data = { text: text };
  $.post("engine.php?a=add", data,function(response){
    new_j = JSON.parse(response);
    $("#item_input").val("");
    let new_item = createItem(new_j.item);
    $("ul#todo_list").append(new_item);
  })

}

// When the DOM is ready, set up the page
$(document).ready(function() {
  console.log("DOM ready!");
  // Ajax query to get JSON list of todo items
  $.ajax({
    dataType: "json",
    url: "engine.php",
    method: "get",
    data: {"a" : "list"}
  }).done(function( data ) {
    /* After there response comes back loop
     * over the array of ToDo items contained
     * in data. The jquery method "each" loops
     * over the item passed as the first 
     * argument and calls the function that is
     * passed as the second argument.
     */
    $.each(data, function (index, item) {
      // call createItem to create a new HTML item
      let new_item = createItem(item);
      // Append sthe new HTML into the ul on the page
      $("#todo_list").append(new_item);
    });
  }).fail(function () {
    $("#error_msgs").html('ERROR: Could not fetch list of ToDo items.')
    .show();
  });

  // Bind event listener to the add button's click event
  $('#submit_btn').click(addClickListener);
});