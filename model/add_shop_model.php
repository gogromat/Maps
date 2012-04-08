<?php
//require_once("includes/controller_included.php");
require_once("../includes/Place.php");
require_once("../includes/Marker.php");

print_var($_REQUEST,1);

die();
$error_string = "";

$status->destination = "search_shop";

if(isset($_POST['description'])) $description = $_POST['description'];
if(isset($_POST['solution_category'])) $marker_category = $_POST['solution_category'];
if(isset($_POST['solution_name'])) $marker_name = $_POST['solution_name'];
if(isset($_POST['factor'])) $factor = $_POST['factor'];

$compound_names = array();
$compound_concentrations = array();
$concentration_units = array();

$pH = array();
$per = array();
$per_units = array();
$per_compound_id = array();
$notes = array();
$step = array();

if (isset($_POST['compound_name']) && isset($_POST['solution_name'])) {
	if (!is_array($_POST['compound_name']) && is_numeric($_POST['compound_name']))
		$compound_names[] = (int)$_POST['compound_name'];

	else if (is_array($_POST['compound_name']))
		$compound_names = $_POST['compound_name'];

	if (!is_array($_POST['compound_concentration']) && is_numeric($_POST['compound_concentration']))
		$compound_concentrations[] = (int)$_POST['compound_concentration'];

	else if (is_array($_POST['compound_concentration']))
		$compound_concentrations = $_POST['compound_concentration'];

	if (!is_array($_POST['concentration_units']) && is_numeric($_POST['concentration_units']))
		$concentration_units[] = (int)$_POST['concentration_units'];

	else if (is_array($_POST['concentration_units']))
		$concentration_units = $_POST['concentration_units'];

	if (!is_array($_POST['pH']) && is_numeric($_POST['pH']))
		$pH[] = (int)$_POST['pH'];

	else if (is_array($_POST['pH']))
		$pH = $_POST['pH'];

    if (!is_array($_POST['per']) && is_numeric($_POST['per']))
		$per[] = (int)$_POST['per'];

	else if (is_array($_POST['per']))
		$per = $_POST['per'];

    if (!is_array($_POST['per_units']) && is_numeric($_POST['per_units']))
		$per_units[] = (int)$_POST['per_units'];

	else if (is_array($_POST['per_units']))
		$per_units = $_POST['per_units'];

    if (!is_array($_POST['per_compound_id']) && is_numeric($_POST['per_compound_id']))
		$per_compound_id[] = (int)$_POST['per_compound_id'];

	else if (is_array($_POST['per_compound_id']))
		$per_compound_id = $_POST['per_compound_id'];

    if (!is_array($_POST['notes']) && is_numeric($_POST['notes']))
		$notes[] = (int)$_POST['notes'];

	else if (is_array($_POST['notes']))
		$notes = $_POST['notes'];

    if (!is_array($_POST['step']) && is_numeric($_POST['step']))
		$step[] = (int)$_POST['step'];

	else if (is_array($_POST['step']))
		$step = $_POST['step'];
}
else {
	return handle_error($status.'Name(s) missing');
}

// ****SOLUTION****
$marker = new Marker();

$marker->name = $name;

$marker->address = $address;

$marker->lat = $lat;

$marker->lng = $lng;

$existed = $marker->exists;

//die();
//begin Transaction
$marker->startTrans();

$result = $marker->save();

if (!$existed) {
	$marker->solution_id = $marker->getLastInsertedId();
}
else {
    foreach(SolutionCompound::getSolutionCompounds(array('solution_id'=>$marker->solution_id)) as $r){
        $r->delete();
    }
}

// Commit Transaction
$marker->commitTrans();

print_var($marker,1);


// **** SOLUTION COMPOUND ****
foreach ($compound_names as $k => $compound_name)
{
	if (isset($compound_name) && !empty($compound_name))
    {
        //if (!is_numeric($compound_name))
        //	continue;
        $markerCompound = new SolutionCompound();

        $markerCompound->startTrans();

        $compound_ids = Compound::getCompounds(array('compound_name'=>$compound_name));

        $markerCompound->solution_id = $marker->solution_id;
        $markerCompound->compound_id = $compound_ids[0]->compound_id;

        $markerCompound->compound_concentration = $compound_concentrations[$k];
        $markerCompound->concentration_units = $concentration_units[$k];

        $markerCompound->pH = $pH[$k];
        $markerCompound->notes = $notes[$k];

        $markerCompound->per = $per[$k];
        $markerCompound->per_units = $per_units[$k];
        $markerCompound->per_compound_id= $per_compound_id[$k];

        $markerCompound->step= $step[$k];

        //if (!$markerCompound->exists)
        //	continue;

        /*if (!$groupPermission->validate())
        {
            $_SESSION[APPLICATION_NAME]['form_data'] = $_POST;
            $status->status = "Error";
            $status->message = "<ul><li>".join("</li><li>",$featureType->errorStack)."</li></ul>";
        }
        else
        {*/
        $markerCompound->solution_id = $marker->solution_id;
        $markerCompound->compound_id = $compound_ids[0]->compound_id;

        //print_var($markerCompound,1);
        //die();

        $result = $markerCompound->save();

        $markerCompound->commitTrans();

        print_var($markerCompound,1);
            // LOG
            //log_entry("Solution", "ADD", $markerCompound->solution_id .' '.$markerCompound->compound_id, $markerCompound);
        //}
	}
}

$status->status = "Success";
$status->message = "Solution Compound was added successfully";
$status->destination="solut";

header('Location:add_solution.php');
print_r($status);
return $status;

?>
