<?php
/**
 * PLUGIN NAME: FHIR session viewer
 * DESCRIPTION: Display the encrypted content of a FHIR session
 * VERSION: 1.0.0
 * AUTHOR: Francesco Delacqua
 */

use Vanderbilt\REDCap\Classes\Fhir\FhirLauncher\DTOs\SessionDTO;
use Vanderbilt\REDCap\Classes\Fhir\FhirLauncher\PersistenceStrategies\SessionStrategy;

// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";

// OPTIONAL: Your custom PHP code goes here. You may use any constants/variables listed in redcap_info().

class FhirSessionViewer {
	function getData() {
		$queryString = "SELECT * FROM redcap_sessions WHERE session_data NOT LIKE '_authsession%' ORDER BY session_expiration DESC LIMIT 5";
		$result = db_query($queryString);
		$rows = [];
		while($row=db_fetch_assoc($result)) $rows[] = $row;
		return $rows;
	}

	function viewData($session_id) {
		$persistanceStrategy = new SessionStrategy();
		$session = SessionDTO::fromState($session_id, $persistanceStrategy);
		if(!($session instanceof SessionDTO)) {
			$message = "The provided ID ($session_id) does not match a FHIR session. Please use a different ID.";
			throw new Exception($message, 400);
		}
		return $session;
	}
}

// OPTIONAL: Display the header
$HtmlPage = new HtmlPage();
$HtmlPage->PrintHeaderExt();

$isAdmin = (defined('SUPER_USER') && SUPER_USER) ?? false;
if(!$isAdmin) {
	$message = 'This page is reserved to REDCap administrators';
}

if($isAdmin) {
	$viewer = new FhirSessionViewer();
	$latestSessions = $viewer->getData();

	if($_POST['view-session']) {
		$sessionIDasText = (trim(@$_POST['session_id_text']) !== '') ? $_POST['session_id_text'] : false;
		$sessionId = (trim(@$_POST['session_id']) !=='') ? $_POST['session_id'] : false;
		$session_id = $sessionIDasText ?: $sessionId;
		try {
			$selectedSession = $viewer->viewData($session_id);
		} catch (\Throwable $th) {
			$message = $th->getMessage();
		}
	}
}

// Your HTML page content goes here
?>
<style>
[data-fhir-session-form] {
	display: grid;
	gap: 1em;
	/* max-width: 500px; */
	margin: 0 auto;
}
main {
	max-width: 780px;
	margin: 0 auto;
}

</style>
<main>
	<h3 style="color:#800000;">
		FHIR session viewer 
	</h3>
	<p>Hello, <?php echo USERID ?>!</p>
	<? if($isAdmin): ?>

		<div class="alert alert-primary mb-4">
			<p>Please select a session from the dropdown menu or enter the session ID in the text field</p>
		</div>

		<div class="card">
			<form class="card-body" data-fhir-session-form action="" method="POST">

				<select class="form-control" name="session_id" id="">
					<option value="">please select a session</option>
					<? foreach($latestSessions as $session) : ?>
					<option value="<?= $session['session_id']  ?>"><?= $session['session_id'] ?> (<?= $session['session_expiration']  ?>)</option>
					<? endforeach; ?>
				</select>

				<input class="form-control" type="text" name="session_id_text" value="">
				<button class="btn btn-sm btn-primary" type="submit" value="1" name="view-session">View</button>
			</form>
		</div>

		<? if($selectedSession instanceof SessionDTO): ?>
		<pre class="mt-2">
			<?= print_r($selectedSession) ?>
		</pre>
		<? endif; ?>
	<? endif; ?>
	<?if($message): ?>
	<pre class="mt-2">
		<?= print_r($message) ?>
	</pre>
	<? endif; ?>
</main>
<?php

// OPTIONAL: Display the footer
$HtmlPage->PrintFooterExt();