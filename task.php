<?php
require 'inc/functions.php';

$pageTitle = "Task | Time Tracker";
$page = "tasks";

//Set each variable to empty string 
//We can use these vairales now even if they are empty
$project_id = $title = $date = $time = '';

//Get id from query string link in task_list.php 
//Use id to pull task details using get project funtion
//Get task function will return an array 
if(isset($_GET['id'])){
    //Use list funtion to add those array values into individual variables
    list($task_id, $title, $date, $time, $project_id) = 
    get_task(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
}


//Receive input through inputs 
//Verify request method is POST 
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //Get task_id in order to pass to add task function 
    $task_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    //Filter input and remove white space from beginning and end of our feilds 
    $project_id= trim(filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT));
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING));
    $time = trim(filter_input(INPUT_POST, 'time', FILTER_SANITIZE_NUMBER_INT));

    //Use explode() takes two parameters a string delimiter and a string returns array
    //Creates 3 item array with each part of the date as an element
    $dateMatch = explode('/',$date);

    //Fields are manditory make sure fields are not empty
    if(empty($project_id) || empty($title) || empty($date) || empty($time)){
        $error_message = 'Please fill in the required fields: Title, Category, 
        Date, and Time';
    /* Check if 3 parts of date are passed and each has the correct # of chars */    
    }elseif(count($dateMatch) != 3 
          || strlen($dateMatch[0]) != 2
          || strlen($dateMatch[1]) != 2
          /* 2001 year format */ 
          || strlen($dateMatch[2]) != 4
          /* Check for valid date pass: Month, Day, Year */ 
          || !checkdate($dateMatch[0], $dateMatch[1], $dateMatch[2]))
          {
        $error_message = 'Invalide Date';
    }else{    
       //Add new task to task list or update 
       if(add_task($project_id, $title, $date, $time, $task_id)){
            //Successful insert (true returned) re-direct to task list page
            header('Location: task_list.php');
            exit;
       }else{
            //Unsuccessful insert (false returned) display error message
            $error_message = 'Could not add task';
       }

    }
}
include 'inc/header.php';
?>

<div class="section page">
    <div class="col-container page-container">
        <div class="col col-70-md col-60-lg col-center">
            <h1 class="actions-header">
            <!-- Change header based on query string received from task_list.php --> 
            <?php
                if(!empty($task_id)){
                    echo 'Update';
                }else{
                    echo 'Add';    
                }
            ?>
            Task</h1>
            <!-- Display error message if input field empty -->
            <?php
            if(isset($error_message)){
                echo "<p class='message'>$error_message</p>";
            }
            ?>
            <form class="form-container form-add" method="post" action="task.php">
                <table>
                    <tr>
                        <th>
                            <label for="project_id">Project</label>
                        </th>
                        <td>
                            <select name="project_id" id="project_id">
                                <option value="">Select One</option>
                                <!-- Pull project_id, title, from projects table -->
                                <?php 
                                foreach(get_project_list() as $item){
                                        echo "<option value='"
                                        . $item['project_id'] . "'";
                                        /* Display values after resubmit */
                                        if($project_id == $item['project_id']){
                                            echo ' selected';
                                        }
                                        echo ">" . $item['title'] . "</option>"; 
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="title">Title<span class="required">*</span></label></th>
                        <td>
                             <!--Input title if page re -->  
                             <!--Display values after resubmit if no project selected escape output -->
                            <input type="text" id="title" name="title" 
                            value="<?php echo htmlspecialchars($title); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="date">Date<span class="required">*</span></label></th>

                        <td>
                            <!-- Display values after resubmit if no project selected escape output -->
                            <input type="text" id="date" name="date" 
                            value="<?php echo htmlspecialchars($date); ?>" placeholder="mm/dd/yyyy" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="time">Time<span class="required">*</span></label></th>
                        <td>
                            <!-- Display values after resubmit if no project selected escape output -->
                            <input type="text" id="time" name="time" 
                            value="<?php echo htmlspecialchars($time); ?>" /> minutes
                        </td>
                    </tr>
                </table>
                 <!-- Add hidden field for task ID to prevent duplicate when updating --> 
                 <?php
                    if(!empty($task_id)){
                        echo "<input type='hidden' name='id' value='$task_id' />";
                    }
                ?>              
                <input class="button button--primary button--topic-php" type="submit" value="Submit" />
            </form>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
