<?php
	$clientManager = new ClientManager($db);
    $taskManager = new TaskManager($db);   
	try {
		$client = $clientManager->session();
	}
	catch (Exception $e) {
		$error[] = $e->getMessage();
	}

	if (isset($client)) {
		$results = '';
        try {
            $myTasks = $taskManager->getAvailableTasks($client->id());


            $count = 0;  
            foreach ($myTasks as $task) {
                if($count % 2 == 0) $results .= '<tr class="odd">';
                else $results .= '<tr class="even">';
                $count++;

                $results .= '<td>' . $task['category'] . '</td>';
                $results .= '<td>' . $task['name'] . '</td>';
                $results .= '<td>' . $task['location'] . '</td>';
                $results .= '<td>' . $task['description'] . '</td>';
                $results .= '<td class = "center">' . $task['salary'] . '</td>';
                $results .= '<td class = "center">' . $task['startdate'] . '</td>';
                $results .= '<td class = "center">' . $task['enddate'] . '</td>';
                $results .= '<td class="center">';
                $results .= '<a href = "./pickTask.php?id=' . $task['id'] . 
                                '&creator=' . $task['creator'] . '">
                                <button type="button" class="btn btn-default">Want this task</button>
                            </a>';
                $results .= '</td>';
                $results .= '</tr>';
            }
        }
        catch (Exception $e) {
				$error[] = $e->getMessage();
        }
        
        include_once('./view/tables.php');
        
	}
    else {
        header("Location: ./index.php");
    }
