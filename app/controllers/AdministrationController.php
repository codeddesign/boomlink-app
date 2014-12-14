<?php
// controls for administrative page
use \Phalcon\Mvc\View;

/**
 *This class is used to provide administratve option to the site.
 */
class AdministrationController extends BaseController
{
    /**
     *This function is used to initialize the page for administrative page.
     */
    public function initialize()
    {
        $this->view->setVar("page", "administration");
    }

    /**
     *This function is used to initialize the fetch users from database.
     */
    public function indexAction()
    {
        $status_domain = new StatusDomain();
        $this->view->setVar('current_domains', $status_domain->getDomainsList());
        $this->view->setVar('get_users', Administration::find());
        
        /*
         * checking for user type. If not master then get it's id
         * and set `user_details` in order to enable the form for editing user information
         * */
        if ($this->session->get("user-role") !== "master") {
            $user_id = $this->session->get("user-id");
            $this->edit_userAction($user_id);
        }
    }

    public function showAction($postId)
    {

    }

    public function email_user($action, $info)
    {
        $config = $this->email_config;
        $result = array(
            "status" => "success",
            "message" => "A confirmation email has been sent to this address: " . $info["email"],
        );

        $mail = new PhpMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->IsHTML(true);

        try {
            //sets from config:
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->SMTPSecure = $config["smtp_secure"]; // sets the prefix to the servier
            $mail->Host = $config["smtp_server"]; // sets smtp as the SMTP server
            $mail->Port = $config["smtp_port"]; // set the SMTP port for the smtp server
            $mail->Username = $config["smtp_username"]; // smtp username
            $mail->Password = $config["smtp_password"]; // smtp password
            $mail->AddReplyTo($config["reply_to_mail"]);
            $mail->SetFrom($config["reply_to_mail"]);

            //on the fly sets:
            $mail->AddAddress($info["email"]);
            $mail->Subject = $config[$action . "_subject"];

            //message body handle:
            $template = implode("", file($config[$action . "_body"]));
            $replace = array("__username__", "__password__");
            $with = array($info["username"], $info["password"]);
            $mail->Body = str_ireplace($replace, $with, $template);

            //send email:
            $mail->Send();
        } catch (phpmailerException $e) {
            $result["status"] = "failed";
            $result["message"] = $e->errorMessage() . " - Please contact admin.";
        }

        return $result;
    }

    public function is_master()
    {
        if ($this->session->get("user-role") === "master") {
            return true;
        }

        return false;
    }

    public function is_admin()
    {
        if ($this->session->get("user-role") === "admin") {
            return true;
        }

        return false;
    }

    /* This function is creating a new user or updates depending on the caller (save_usersAction() / edit_userAction) */
    public function common_action($email_action, $administration, $request, $post)
    {
        //sets:
        if (isset($post["username"])) {
            //we are checking here for array's index, because normal users can't update their username.
            $username = $post["username"];
            if (!empty($username)) {
                $administration->username = $username;
            }
        }

        if (isset($post["username"])) {
            $password = $post["password"];
            if (!empty($password)) {
                $administration->password = md5($password);
            }
        }
        if (isset($post["type"])) {
            $user_type = $post["type"];
            if (!empty($user_type)) {
                $administration->type = $user_type; // @string: master / normal
            }
        }

        if (isset($post["limits"])) {
            $limits = $post["limits"];
            if (!empty($limits)) {
                $administration->limits = $limits; // @string: false / true
            }
        }

        if (isset($post["pages"])) {
            $pages = $post["pages"];
            $administration->pages = json_encode($pages, true); // @array: json
        }

        if (isset($post["editing"])) {
            $editing = $post["editing"];
            if (!empty($editing)) {
                $administration->editing = $editing; // @string: false / true
            }
        }

        if (isset($post["domains"])) {
            $domains = $post["domains"];
            $administration->domains = json_encode($domains);
        }

        if (isset($post["email"])) {
            $email = $post["email"];
            if (!empty($email)) {
                $administration->email = $email;

                if ($email_action !== false) {

                    $info = array(
                        "username" => $username,
                        "password" => $password,
                        "email" => $email,
                    );

                    $result = $this->email_user($email_action, $info);
                    //echo $result["message"] . "<br/>"; // - disabled
                }
            }
        }

        //change values depending on cases - NOTE - don't forget about "type of editing" in the future:
        $empty = array();
        if ($administration->type === "admin") {
            $administration->limits = "false";
            $administration->pages = json_encode($empty);
            $administration->editing = "true";
            $administration->domains = json_encode($empty);
        }

        if ($administration->limits === "false") {
            $administration->pages = json_encode($empty);
        }

        if ($administration->editing === "false") {
            $administration->domains = json_encode($empty);
        }

        return $administration;
    }


    /**
     *This function is used to allow for user data to be saved within database.
     */
    public function save_usersAction()
    {
        $request = $this->request;

        if ($request->isPost()) {
            $save_session = new Administration();
            $post = $request->getPost();

            //action:
            $next_action = false;
            if (!empty($post["email"])) {
                $next_action = "new_user";
            }

            $save_session = $this->common_action($next_action, $save_session, $request, $post);

            //gets action message:
            if ($save_session->save() == false) {
                foreach ($save_session->getMessages() as $message) {
                    echo $message->getMessage() . '<br>';
                }
            } else {
                $this->flash->success('The user is saved successfully.');
            }
        }

        $get_users = Administration::find();
        $this->view->setVar('get_users', $get_users);
    }

    /**
     *This function is used to delete uer from database (admin is prohibited).
     */
    public function delete_userAction($postid)
    {
        $user = Administration::findFirst("id = '$postid'");
        $msg = "";

        if (!empty($user)) {
            if (($user->type === "master")) {
                $msg = "Failed to remove user: no credentials.";
                echo json_encode(array("deleted" => "sucss", "msg" => $msg));
                exit;
            }

            if ($user->delete() == false) {
                $msg = "Sorry, we can't delete the user right now: <br/>";
                foreach ($user->getMessages() as $message) {
                    echo $message . ", <br/>";
                }
            } else {
                $msg = "The user " . $user->username . " was deleted successfully!";
            }
        } else {
            $msg = "Sorry, we can't find this user. \n";
        }

        echo json_encode(array("deleted" => "sucss", "msg" => $msg));
        exit;
    }

    /**
     *This function is used to edit user information.
     */
    public function edit_userAction($postid)
    {
        $q = "id = '$postid'";
        $user = Administration::findFirst($q);

        if (empty($user)) {
            echo "Sorry, we can't find this user. \n";
        } elseif (!$this->is_master() AND $user->type === "master") {
            echo "Failed to edit user: no credentials.";
        } else {
            $request = $this->request;
            if ($request->isPost()) {
                $update_session = Administration::findFirst($q);
                $post = $request->getPost();

                //action:
                $next_action = false;
                if (!empty($post["email"]) && $post["email"] !== $update_session->email) {
                    //$next_action = "new_mail"; // - disabled
                }

                //save information & send email
                $update_user = $this->common_action($next_action, $update_session, $request, $post);

                if ($update_user->save() === false) {
                    echo "Sorry, we can't update the user's information right now: <br/>";
                    if (is_array($update_user->getMessages())) {
                        foreach ($update_user->getMessages() as $message) {
                            echo $message . ", <br/>";
                        }
                    }
                } else {
                    $user = Administration::findFirst($q);
                    echo "<b>" . ucfirst($user->username) . "</b>'s information was updated successfully!";
                }
            }

            $this->view->setVar('user_details', $user);
        }

        $get_users = Administration::find();
        $this->view->setVar('get_users', $get_users);
    }
}