<?php
require_once(__DIR__ . '/../../../config.php'); // include Moodle configuration file
require_once($CFG->dirroot . '/lib/authlib.php'); // include Moodle authentication library
global $SESSION, $CFG, $PAGE, $OUTPUT;

// Validate that 'username' and 'password' values are present
if (empty($_POST['username']) || empty($_POST['password'])) {
    $response = array(
        'success' => false,
        'message' => 'Missing username or password',
    );
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($response);
    exit;
}

// Set the username and password from the API
$username = $_POST['username'];
$password = $_POST['password'];

$token = \core\session\manager::get_login_token();
$user = authenticate_user_login($username, $password, false, $reason, $token);
if (!$user) {
    // The authentication failed
    $response = array(
        'success' => false,
        'message' => 'Authentication failed',
    );
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($response);
    exit;
}

/// Define variables used in page
$site = get_site();


// Ignore any active pages in the navigation/settings.
// We do this because there won't be an active page there, and by ignoring the active pages the
// navigation and settings won't be initialised unless something else needs them.
$PAGE->navbar->ignore_active();
$loginsite = get_string("loginsite");
$PAGE->navbar->add($loginsite);


// Check for password expiration
$userauth = get_auth_plugin($user->auth);
if (!isguestuser() && !empty($userauth->config->expiration) && $userauth->config->expiration == 1) {
    $externalchangepassword = false;
    if ($userauth->can_change_password()) {
        $passwordchangeurl = $userauth->change_password_url();
        if (!$passwordchangeurl) {
            $passwordchangeurl = $CFG->wwwroot . '/login/change_password.php';
        } else {
            $externalchangepassword = true;
        }
    } else {
        $passwordchangeurl = $CFG->wwwroot . '/login/change_password.php';
    }
    $days2expire = $userauth->password_expire($user->username);
    $PAGE->set_title("$site->fullname: $loginsite");
    $PAGE->set_heading("$site->fullname");
    if (intval($days2expire) > 0 && intval($days2expire) < intval($userauth->config->expiration_warning)) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('auth_passwordwillexpire', 'auth', $days2expire), $passwordchangeurl, $urltogo);
        echo $OUTPUT->footer();
        exit;
    } elseif (intval($days2expire) < 0) {
        if ($externalchangepassword) {
            // We end the session if the change password form is external. This prevents access to the site
            // until the password is correctly changed.
            require_logout();
        } else {
            // If we use the standard change password form, this user preference will be reset when the password
            // is changed. Until then, it will prevent access to the site.
            set_user_preference('auth_forcepasswordchange', 1, $user);
        }
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('auth_passwordisexpired', 'auth'), $passwordchangeurl, $urltogo);
        echo $OUTPUT->footer();
        exit;
    }
}

// Log in the user
$login_result = complete_user_login($user);
\core\session\manager::apply_concurrent_login_limit($user->id, session_id());
if (!$login_result) {
    // The login failed
    $response = array(
        'success' => false,
        'message' => 'Login failed',
    );
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($response);
    exit;
}

// sets the username cookie
if (!empty($CFG->nolastloggedin)) {
     // auth plugins can temporarily override this from loginpage_hook()
     // do not save $CFG->nolastloggedin in the database!
} else if (empty($CFG->rememberusername)) {
   // no permanent cookies, delete old one if exists
    set_moodle_cookie('');
} else {
    set_moodle_cookie($username);
}

$SESSION->wantsurl = $CFG->wwwroot . '/my';
$url = new moodle_url('/my');

$response = array(
    'success' => true,
    'message' => 'Autologin successful',
    'redirect' => $CFG->wwwroot . '/my',
);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
// Redirect the user
header('Location: ' . $CFG->wwwroot . '/my');
echo json_encode($response);
exit;
