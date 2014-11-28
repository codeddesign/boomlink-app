<?php
use \Phalcon\Mvc\View;
use \Phalcon\Db;

class ProxyController extends BaseController
{
    public function initialize()
    {
        //needed!
        $this->view->setVar("page", "proxy");

        //get all proxies from table:
        $result = $this->get_current_proxies();

        //set views:
        $this->view->setVar("proxies_list", $result);
    }

    public function get_current_proxies()
    {
        $result = $this->db->fetchAll("SELECT * FROM proxies_list", Db::FETCH_ASSOC);
        if (count($result) == 0) {
            return array();
        }

        return $result;
    }

    public function IndexAction()
    {

    }

    public function uploadAction()
    {
        $this->view->disable();
        echo "<pre>";
        $temp_dir = "temp_files/";
        $request = $this->request;

        if ($request->isPost()) {
            $action = $request->getPost("action");
            switch ($action) {
                case "file_text":
                    if (!$request->hasFiles()) {
                        exit("Failed to upload file.");
                    }

                    foreach ($request->getUploadedFiles() as $file) {
                        $file_name = $file->getName();

                        $file_path = $temp_dir . $file_name . "_" . time();
                        $file->moveTo($file_path);
                        $n_proxies = $this->get_from_file($file_path);

                        //remove uploaded file:
                        unlink($file_path);
                    }
                    break;
                case "list_text":
                    $textarea = $request->getPost("list_text");
                    $n_proxies = $this->get_from_textarea($textarea);
                    break;
            }
        }

        //check for new proxies:
        if (!isset($n_proxies)) {
            exit("Failed - No file selected / list inserted.");
        }

        $n_proxies = $this->parse_proxies($n_proxies);
        if (count($n_proxies) < 1) {
            exit("Failed - no results in your list matching the format -> ip:port:user:pass");
        }

        //get current proxies from db:
        //$c_proxies = $this->get_current_proxies();

        $query = "INSERT INTO proxies_list (ProxyIP, ProxyPort, user, password) VALUES ";
        foreach ($n_proxies as $info) {
            $query .= "('" . $info["ProxyIP"] . "', '" . $info["ProxyPort"] . "', '" . $info["user"] . "', '" . $info["password"] . "'), ";
        }

        //remove last ",":
        $query = substr($query, 0, strrpos($query, ","));

        //query fallback case:
        $query .= " ON DUPLICATE KEY UPDATE ProxyPort=VALUES(ProxyPort), user=VALUES(user), password=VALUES(password)";

        //save proxies:
        $result = $this->db->execute($query);
        if ($result) {
            exit("Proxies - Successfully added.");
        } else {
            exit("Action to add/update FAILED (contact admin).");
        }
    }

    public function get_from_file($file_path)
    {
        $list = file($file_path);

        if (is_array($list)) {
            $proxies = array();
            foreach ($list as $l_no => $line) {
                $line = trim($line);
                if (strlen($line) > 0) {
                    $proxies[] = $line;
                }
            }

            return $proxies;
        } else {
            return array();
        }
    }

    public function get_from_textarea($content)
    {
        $list = explode("\n", $content);
        $proxies = array();

        foreach ($list as $l_no => $line) {
            $line = trim($line);
            if (strlen($line) > 0) {
                $proxies[] = $line;
            }
        }


        return $proxies;
    }

    public function parse_proxies($proxies)
    {
        $new_list = array();

        foreach ($proxies as $p_no => $proxy) {
            $parsed = explode(":", $proxy);
            if (count($parsed) === 4) {
                list($ip, $port, $user, $pass) = explode(":", $proxy);
                $new_list[$p_no]["ProxyIP"] = $ip;
                $new_list[$p_no]["ProxyPort"] = $port;
                $new_list[$p_no]["user"] = $user;
                $new_list[$p_no]["password"] = $pass;
            }
        }

        return $new_list;
    }

    public function deleteAction($id)
    {
        //needed:
        $this->view->disable();

        $query = "SELECT idx FROM proxies_list WHERE idx='" . $id . "'";
        $result = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        if (count($result) < 1) {
            $status = "failed";
            $msg = "Failed to remove proxy.";
        } else {
            $query = "DELETE FROM proxies_list WHERE idx='" . $id . "'";
            $result = $this->db->execute($query);
            if ($result) {
                $status = "sucss";
                $msg = "Proxy was removed successfully.";
            }
        }

        echo json_encode(array("deleted" => $status, "msg" => $msg));
    }
}