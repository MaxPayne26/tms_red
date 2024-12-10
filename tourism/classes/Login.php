<?php
require_once '../config.php';

class Login extends DBConnection {
    private $settings;

    public function __construct(){
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
        ini_set('display_error', 1);
    }

    public function __destruct(){
        parent::__destruct();
    }

    public function index(){
        echo "<h1>Access Denied</h1> <a href='".base_url."'>Go Back.</a>";
    }

    public function login(){
        extract($_POST);

        // Query to check if the user exists with the provided username and password
        $qry = $this->conn->query("SELECT * from users where username = '$username' and password = md5('$password') ");
        if ($qry->num_rows > 0) {
            $user = $qry->fetch_array();
            
            // Store user data in session
            foreach ($user as $k => $v) {
                if (!is_numeric($k) && $k != 'password') {
                    $this->settings->set_userdata($k, $v);
                }
            }

            // Check if the logged-in user is 'admin'
            if ($this->settings->userdata('username') === 'admin') {
                $this->settings->set_userdata('login_type', 1);
                return json_encode(array('status' => 'success', 'message' => 'Admin login successful.'));
            } else {
                // If not admin, deny access
                return json_encode(array('status' => 'failed', 'message' => 'Only admin can access this page.'));
            }
        } else {
            return json_encode(array('status' => 'incorrect', 'last_qry' => "SELECT * from users where username = '$username' and password = md5('$password') "));
        }
    }

    public function logout(){
        if ($this->settings->sess_des()) {
            redirect('admin/login.php');
        }
    }

    // Admin-specific function
    function login_user(){
        extract($_POST);

        // Query to check if the user exists and is of type 0 (regular user)
        $qry = $this->conn->query("SELECT * from users where username = '$username' and password = md5('$password') and type = 0 ");
        if ($qry->num_rows > 0) {
            foreach ($qry->fetch_array() as $k => $v) {
                $this->settings->set_userdata($k, $v);
            }
            $this->settings->set_userdata('login_type', 1);
            $resp['status'] = 'success';
        } else {
            $resp['status'] = 'incorrect';
        }

        if ($this->conn->error) {
            $resp['status'] = 'failed';
            $resp['_error'] = $this->conn->error;
        }
        return json_encode($resp);
    }
}

// Handle the actions (login, login_user, logout, etc.)
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();
switch ($action) {
    case 'login':
        echo $auth->login();
        break;
    case 'login_user':
        echo $auth->login_user();
        break;
    case 'logout':
        echo $auth->logout();
        break;
    default:
        echo $auth->index();
        break;
}
?>
