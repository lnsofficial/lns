<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'AprilEnquete.php' );

class AprilEnqueteController extends BaseController{

    public function __construct(){
    }

    public function vote(){
        $data = [];
        if (
            !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            && (!empty($_SERVER['SCRIPT_FILENAME']) && '/AprilEnquete/addCount' === basename($_SERVER['SCRIPT_FILENAME']))
            ) {
            header('Content-Type: application/json');
            
            $data["result"] = "ERROR";
            
            echo json_encode( $data );
            
            exit();
        }
        
        $size   = $_REQUEST["size"];
        
        $db = new Db();
        $db->beginTransaction();
        
        $AprilEnquete = new AprilEnquete( $db, 1 );
        
        $AprilEnquete->boin = $AprilEnquete->boin + 1;
        
        if( $size == 2 ){
            $AprilEnquete->cinderella = $AprilEnquete->cinderella + 1;
        }
        
        $AprilEnquete->save();
        
        $db->commit();
        
        $data["result"] = "OK";
        
        header('Content-Type: application/json');
        echo json_encode( $data );
    }

    public function getCount(){
        $db = new Db();
        $ApirlEnquete = new AprilEnquete( $db, 1 );
        
        header('Content-Type: application/json');
        
        $data[]   = $ApirlEnquete->boin;
        $data[]   = $ApirlEnquete->cinderella;
        
        echo json_encode( $data );
    }
}
