# plugin-fhir-session-viewer

This plugin can help troubleshoot problems related to the CDIS authentication process.

During authentication, an encrypted session is created and stored in the redcap_sessions table, in the REDCap's database.

PLEASE NOTE: a FHIR session, differently from a standard authentication session, DOES NOT START with '_authsession'.

To inspect the content of a session:

* install the plugin in your REDCap instance
* visit the plugin page (e.g., https://redcap.test/plugins/view-fhir-session)
* select the session from the dropdown menu or type the session_id in the input field
click on "View"
