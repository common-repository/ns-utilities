<?php

class ns_object{

    public $id;
    public $object_type;
    public $value;
    public $related_with;
    public $external_keys;
    public $active;

    
    
    
    function __construct() {
        if ( $this->getId() > 0){
            $this->unserialize_fields();
        }
    }
    
    private function serialize_fields(){
        $this->value = base64_encode(serialize($this->value));
        $this->external_keys =  base64_encode(serialize($this->external_keys));        
    }
    
    private function unserialize_fields(){
        $this->value = unserialize(base64_decode($this->value));
        $this->external_keys = unserialize(base64_decode($this->external_keys));     
    }
    
    public function getId(){
        return $this->id;
    }
    
    public function deactivate(){
        $this->active = 0;
        return $this->save();
    }
    
    public function save(){
        $this->serialize_fields();
        if ( $this->getId() ){
            $ret = $this->update();
        }
        else {
            $ret = $this->insert();
        }
        $this->unserialize_fields();
        return $ret;
    }
    
    private function insert(){
        $tablename = ns_object::get_table_name();      
        $sqlStr = "INSERT INTO $tablename (object_type,value,related_with,external_keys,active) VALUES ('".$this->object_type."','".$this->value."','".$this->related_with."','".$this->external_keys."','".$this->active."')";
        mysql_query($sqlStr) or die (mysql_error());
        //Retrieve Inserted ID:
        $sqlStr = "SELECT * FROM $tablename WHERE object_type='".$this->object_type."' AND value='".$this->value."' AND related_with='".$this->related_with."' AND external_keys='".$this->external_keys."' AND active='".$this->active."' ORDER BY id DESC";
        $rs = mysql_query($sqlStr);
        if ($rs && mysql_num_rows($rs)> 0){
            $row = mysql_fetch_object($rs);
            $this->id = $row->id;
            return $this->getId();
        }
        return false;
    }
    
    private function update(){
        $tablename = ns_object::get_table_name();            
        $sqlStr = "UPDATE $tablename SET object_type='".$this->object_type."',value='".$this->value."',related_with='".$this->related_with."',external_keys='".$this->external_keys."',active='".$this->active."' WHERE id='".$this->getId()."'";
    }
    
    
    public function getObj(){
        return $this->value;
    }
    
    public function setObj($obj){
        $this->value = $obj;
    }
    
    public function isActive(){
        return $this->active;
    }
    
    public function remove($delete_from_db = false){
        if ($this->getId() > 0){
            if ($delete_from_db){
                $tablename = ns_object::get_table_name();
                $sqlStr = "DELETE FROM $tablename WHERE id='".$this->getId() ."'";
                return mysql_query($sqlStr);
            }
            else {
                return $this->deactivate();
            }
        }
        else {
            return false;
        }
    }
    
    static function create($object_type,$value,$related_with = "",$external_keys = false,$active = true,$save = true){
        $nso = new ns_object();
        $nso->object_type = $object_type;
        $nso->value = $value;
        $nso->related_with = $related_with;
        $nso->external_keys = $external_keys;
        $nso->active = $active;
        if ($save){
            if ( !$nso->save() ){
                return false;
            }
        }
        return $nso;
    }
    
    static function get_by_id($obj_id){
        $tablename = ns_object::get_table_name();            
        $sqlStr = "SELECT * FROM $tablename WHERE id='{$obj_id}'";
        return ns_object::get_object_by_sql($sqlStr);
    }
    
    static function get_object_by_sql($sqlStr){
        $rs = mysql_query($sqlStr);
        if ($rs && mysql_num_rows($rs)> 0){
            $nso = mysql_fetch_object($rs,'ns_object');
            return $nso;
        }
        return false;
        
    }
    
    static function get_objects_by_sql($sqlStr){
        $rs = mysql_query($sqlStr);
        $objs = array();
        if ($rs && mysql_num_rows($rs)> 0){
            while ($nso = mysql_fetch_object($rs,'ns_object')){
                $objs[] = $nso;    
            }
            
        }
        return $objs;
    }
    

    static function get_table_name(){
        global $wpdb;
        global $nslt_dbVersion;
        $table_name = $wpdb->prefix . "ns_objects";
        return  $table_name;
    }
    
    
}




  
?>