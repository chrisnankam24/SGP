<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/8/2016
 * Time: 8:08 AM
 */
class Helper extends CI_Controller {

    function _construct()
    {
        parent::__construct();

    }
    
    /**
     * API to perform Problem report request
     */
    public function uploadFile(){
        $timeexec = time();
        $temps = date('Y_m_d_',$timeexec);
        if(isset($_FILES['fileToUpload'])){
            $chemin = "uploads/";
            $link_file = $chemin . $temps . basename($_FILES["fileToUpload"]["name"]);
            $uploadState = 1;
            $imageFileType = pathinfo($link_file,PATHINFO_EXTENSION);
            // Check if file already exists
            if (file_exists($link_file)) {
                echo "Sorry, file already exists.";
                $uploadState = 0;
            }
            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 500000) {
                echo "Sorry, your file is too large.";
                $uploadState = 0;
            }
            // Allow certain file formats
            if($imageFileType != "csv" && $imageFileType != "xlsx" && $imageFileType != "xls") {
                echo "Sorry, only csv, xls & xlsx files are allowed.";
                $uploadState = 0;
            }
            // Check if $uploadState is set to 0 by an error
            if ($uploadState == 0) {
                echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $link_file)) {
                    echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }else{
            echo "aucun fichier";
        }
    }

}