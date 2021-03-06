<?php
//application functions

function get_project_list(){
    //Include db connction
    include 'connection.php';

    //Select from the projects table and add exception handling
    try {
    return $db->query('SELECT project_id, title, category FROM projects ');
    } catch (Exception $e){
        echo "Error!: " . $e->getMessage() . "</br>";
        return array();
    }

}

function get_task_list($filter = null){
    //Include db connection
    include 'connection.php';

    //Pull task information
    $sql = 'SELECT tasks.*, projects.title as project FROM tasks'
        . ' JOIN projects ON tasks.project_id = projects.project_id';
    
    //Add where clause to work with dropdown menu in reports
    $where ='';
    
    //Only use where clause if filter parameter is array 
    if(is_array($filter)){
        switch($filter[0]) {
            case 'project':
                $where = ' WHERE projects.project_id = ?';
                break;
            case 'category':
                $where = ' WHERE category = ?';
                break;
            //Add case that checks if record is between ranges        
            case 'date':
                $where = ' WHERE date >= ? AND date <= ?';  
                break; 
            }
        }

    //Order tasks by date    
    $orderBy = ' ORDER BY date DESC';

    //If filter parameter is not null change orderBy 
    if($filter){
        $orderBy = ' ORDER BY projects.title ASC, date DESC';
    }
    try {
    //Concantenate SQL statments together and prepare    
    $results = $db->prepare($sql . $where . $orderBy);
        //Bind parameters 
        if(is_array($filter)){
            $results->bindValue(1, $filter[1]);
        //Bind dates if filter = 'date
        if($filter[0]== 'date'){
            $results->bindValue(2, $filter[2], PDO::PARAM_STR); 
        }
    }
    $results->execute();
    } catch (Exception $e){
        echo "Error!: " . $e->getMessage() . "</br>";
        return array();
    }
    
    //Feth as associative array 
    return $results->fetchAll(PDO::FETCH_ASSOC);

}

//Add optional product_id parameter 
function add_project($title, $category, $project_id = null){
    include 'connection.php';
    //Create SQL statement to update project when $project_id value not nulll
    if($project_id) {
        $sql = 'UPDATE projects SET title = ?, category = ? WHERE project_id = ?';
    } else {
    //Insert record into title and cateogry
    //Value placeholders 
    $sql = 'INSERT INTO projects(title, category)  VALUES(?, ?)';
    }
    try {
        //Pass $sql insert into prepared statement
        $results = $db->prepare($sql);
        //Bind $title argument to value placeholder and define parameter
        $results->bindValue(1,$title, PDO::PARAM_STR);
        //Bind $category argument to value placeholder and define parameter
        $results->bindValue(2,$category, PDO::PARAM_STR);
         //Bind optional project_id argument to value placeholder and define parameter
        if($project_id){
        $results->bindValue(3,$project_id, PDO::PARAM_INT);
        }
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    return true;
}

function get_project($project_id){
    include 'connection.php';
    $sql = 'SELECT * FROM projects WHERE project_id = ?';

    try {
        //Pass $sql insert into prepared statement
        $results = $db->prepare($sql);
        //Bind $project_id argument to value placeholder and define parameter
        $results->bindValue(1,$project_id, PDO::PARAM_INT);
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    return $results->fetch();
}

function delete_project($project_id){
    include 'connection.php';
    //Ensure that projects with tasks cannot be deleted 
    //By not selecting any project that has an id in the tasks table
    $sql = 'DELETE FROM projects WHERE project_id = ?'
    . ' AND project_id NOT IN (SELECT project_id FROM tasks)';
    

    try {
        //Pass $sql delete into prepared statement
        $results = $db->prepare($sql);
        //Bind $project_id argument to value placeholder and define parameter
        $results->bindValue(1,$project_id, PDO::PARAM_INT);
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    //Check if any rows were changed by delete 
   if($results->rowCount() > 0){
        return true; 
   }else{
        return false; 
   }
    
}

function get_task($task_id){
    include 'connection.php';
    //Modify Select to pull fields in the order we specify 
    $sql = 'SELECT task_id, title, date, time, project_id FROM tasks WHERE task_id = ?';

    try {
        //Pass $sql insert into prepared statement
        $results = $db->prepare($sql);
        //Bind $project_id argument to value placeholder and define parameter
        $results->bindValue(1,$task_id, PDO::PARAM_INT);
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    return $results->fetch();
}

function delete_task($task_id){
    include 'connection.php';
    //Modify Select to pull fields in the order we specify 
    $sql = 'DELETE FROM tasks WHERE task_id = ?';

    try {
        //Pass $sql delete into prepared statement
        $results = $db->prepare($sql);
        //Bind $task_id argument to value placeholder and define parameter
        $results->bindValue(1,$task_id, PDO::PARAM_INT);
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    //Return true when records are deleted
    return true;
}

//Add optional task id parameter 
function add_task($project_id, $title, $date, $time, $task_id = null){
    include 'connection.php';
    //Create SQL statement to update project when $task_id value not null
    if($task_id){
        $sql = 'UPDATE tasks SET project_id = ?, title = ?, date = ?, time = ? WHERE task_id = ?';
    } else {
    //Insert record into project_id title date and time
    //Value placeholders 
    $sql = 'INSERT INTO tasks(project_id, title, date, time)  
            VALUES(?, ?, ?, ?)';
    }
    try {
        //Pass $sql insert into prepared statement
        $results = $db->prepare($sql);
        //Bind argument to value placeholder and define parameter
        $results->bindValue(1,$project_id, PDO::PARAM_INT);
        $results->bindValue(2,$title, PDO::PARAM_STR);
        $results->bindValue(3,$date, PDO::PARAM_STR);
        $results->bindValue(4,$time, PDO::PARAM_STR);
        //Bind optional project_id argument to value placeholder and define parameter
        if($task_id){
        $results->bindValue(5,$task_id, PDO::PARAM_INT);
        }
        //Execute the query
        $results->execute();
    }catch (Exception $e){
        echo "Error: " . $e->getMessage() . "<br /> ";
        return false;
    }
    return true;
}