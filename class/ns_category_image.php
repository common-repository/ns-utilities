<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ns_category_image
 *
 * @author joseantonio
 */

/*
 * Esta clase tiene a su disposicion
 * las constantes
 *  NS_CAT_IMAGES_BASE_PATH
 *  NS_CAT_IMAGES_BASE_URL
 */



class ns_category_image {

    private $category_id;
    private $extension;
    private $url_;
    private $path_;
    private $url_original;
    private $path_original;

    /* salva el objeto serializado en la tabla de metas de ns_category_meta*/

    function save(){       
        return update_category_meta($this->category_id,'ns_category_image',$this);
    }

    /*
     * Borra la imagen original y los archivos generados a partir de ella ,
     * tambien elimina la tupla de la tabla ns_category_meta relacionada
     */
    function delete(){
        //echo '<br />in delete';
        $mask = constant("NS_CAT_IMAGES_BASE_PATH")."category_image_".$this->category_id."*";
        $delete_arr = array_map( "unlink", glob( $mask ) );
        
        if ($delete_arr && is_array($delete_arr)){
            foreach ($delete_arr as $d){
                if(!$d){
                  throw new Exception("<br /> Problem image not delete <br />");
                }
            }
        }

        if (!delete_category_meta($this->category_id,'ns_category_image')){
            throw new Exception("<br /> Not delete category meta <br />");
        }

        return true;
        
    }

    private function create_image_format($format){

        $pos_last_point = strrpos($this->path_original,'.');
        $extension = substr($this->path_original, $pos_last_point+1);

        if(is_numeric($format)){
            $width=$format;
            $height=$format;
            $iar=false;
        }elseif(is_array($format)){
            $width=$format["width"];
            $height=$format["height"];
            $iar=true;
        }else{
                throw new Exception("<br /> Error: image format <br />");
        }



        if (!file_exists($this->path_.'_'.$width.'x'.$height.'.'.$extension)){

            //create phpThumb object
            $phpThumb = new phpThumb();
            $phpThumb->aoe = true;//las escala de una mas pequeña a una mas grande
            
            $phpThumb->setSourceFilename($this->path_original);

            $phpThumb->setParameter('iar', $iar); //sin este parametro no nos deja deformar la imagen
            $phpThumb->setParameter('w', $width);
            $phpThumb->setParameter('h', $height);

            
           
            $phpThumb->setParameter('config_output_format', $extension);
       //     $phpThumb->setParameter('config_imagemagick_path', constant("NS_CAT_IMAGES_BASE_PATH").'ns_category_images');
            //$phpThumb->setParameter('config_allow_src_above_docroot', true);

            //$phpThumb->setParameter('config_document_root', '/home/groups/p/ph/phpthumb/htdocs/');
            $phpThumb->setParameter('config_cache_directory', dirname(__FILE__).'/phpThumb_1.7.9/cache/');

            $output_filename = 'category_image_'.$this->category_id.'_'.$width.'x'.$height.'.'.$phpThumb->config_output_format;

            if ($phpThumb->GenerateThumbnail()) { // this line is VERY important, do not remove it!

                $output_size_x = ImageSX($phpThumb->gdimg_output);
                $output_size_y = ImageSY($phpThumb->gdimg_output);

                if ($output_filename ) {

                    if ($phpThumb->RenderToFile(constant("NS_CAT_IMAGES_BASE_PATH").$output_filename)) {
                        // do something on success
                        return constant("NS_CAT_IMAGES_BASE_URL").$output_filename;
                        //echo '<br/> * ';
                        //echo ' Successfully rendered:<br><img src="'.constant("NS_CAT_IMAGES_BASE_URL").$output_filename.'">';
                    } //else {
    //                    // do something with debug/error messages
    //                    echo '<br />** ';
    //                    echo ' Failed (size='.$format.'):<pre>'.implode("\n\n", $phpThumb->debugmessages).'</pre>';
    //                }

                } //else {
    //                $phpThumb->OutputThumbnail();
    //            }

            } //else {
    //            //do something with debug/error messages
    //            echo 'Failed (size='.$format.').<br>';
    //            echo '<div style="background-color:#FFEEDD; font-weight: bold; padding: 10px;">'.$phpThumb->fatalerror.'</div>';
    //            echo '<form><textarea rows="10" cols="60" wrap="off">'.htmlentities(implode("\n* ", $phpThumb->debugmessages)).'</textarea></form><hr>';
    //        }


            return false;
        }
        else{
            return $this->url_.'_'.$width.'x'.$height.'.'.$extension;
        }
    }
    
    /**
     * Nos retorna la url de la imagen en ese formato si no esta la crea.
     * @param <type> $formato
     * @return <type>
     */

    function get_image_url($format='original'){

        
        if($format=='original'){       
            $url= $this->url_original;
        }else{         
            $url=$this->create_image_format($format);

        } 

        return $url;
    }
    
   /**
    * Nos retorna el path de la imagen en ese formato si no esta la crea.
    * @param <type> $formato
    * @return <type>
    */
    private function get_image_path($formato){
        if($formato=='original'){
            $path=$this->path_original;
        }elseif(is_numeric($formato)){

            $path=$this->path_.'_'.$format.'x'.$format.'.'.$this->extension;
            if (!file_exists($path)){
                if (!$this->create_image_format($format)){
                    throw new Exception("<br />Image format not created");
                }
            }
        }elseif(is_array($formato)){
            $path=$this->path_.'_'.$format["width"].'x'.$format["height"].'.'.$this->extension;
            if (!file_exists($path)){
                if (!$this->create_image_format($format)){
                    throw new Exception("<br />Image format not created");
                }
            }
        }else{
            throw new Exception("<br /> Error: image format <br />");
        }
        return $path;
    }




    /**
     * Crea un nuevo objeto de la clase y lo salva en la base de datos
     * @param integer $cat_id
     * @param $category ; es un objeto que proporciona wp
     * @param $image_file; contendra la variable global $_FILES del formulario
     * @return ns_category_image
     */
    static function create_category_image($extension,$image_file , $category = false){

        //echo '<br />'.$image_file.' - '.serialize($category);


        //echo '*********** '.$extension;
        $category_image = new ns_category_image();
        $category_image->extension=$extension;
        
        //if($category && is_int($category) ){
        if($category &&  is_numeric($category) ){
            //echo '1';
            $category_image->category_id =  $category;
        }elseif($category){
           // echo '2';
            $category_image->category_id = $category->term_id;
        }else{
           // echo '3';
            throw new Exception("<br />No cateory ID Specified!!");
        }


        $category_image->url_ = constant("NS_CAT_IMAGES_BASE_URL").'category_image_'.$category_image->category_id;
        $category_image->path_ = constant("NS_CAT_IMAGES_BASE_PATH").'category_image_'.$category_image->category_id;

        //copia la imagen a la nueva ruta        
        $category_image->url_original = constant("NS_CAT_IMAGES_BASE_URL").'category_image_'.$category_image->category_id.'_original.'.$extension;
        $category_image->path_original = constant("NS_CAT_IMAGES_BASE_PATH").'category_image_'.$category_image->category_id.'_original.'.$extension;

        //echo '<br />'.$category_image->url_original;
       // echo '<br />'.$category_image->path_original;
        
        if(file_exists($category_image->path_original)){
            //echo '<br />exit=>delete';
            $category_image->delete();
        }

        //echo 'ttt';
        if (!copy($image_file, $category_image->path_original)) {
            throw new Exception("<br /> No create new image $image_file to ".$category_image->path_original.'<br />');
        }
 
        if ($category_image->save()){           
           return $category_image;
        }else{
            return null;
        }
    }


     static function delete_image($category) {
        $bool=false;

        if($category && is_numeric($category) ){
            //echo '1';
            $category_id =  $category;
        }elseif($category){
           // echo '2';
            $category_id = $category->term_id;
        }else{
           // echo '3';
            throw new Exception("<br />No cateory ID Specified!!");
        }

        $category_image=ns_category_image::get_image_category($category) ;
        if($category_image){
            
            $bool= $category_image->delete();
        }
        return $bool;
     }

    /**
     * Esta funcion nos devuelve un objeto de la clase ns_category_image,
     * partiendo del identificador de su clase.
     * @param Integer or ns_category_image $category
     * @return ns_category_image $category_image
     */
    static function get_image_category($category){

        if(is_numeric( $category) ){
            $category_id =  $category;
        }elseif($category){
            $category_id = $category->term_id;
        }else{
            throw new Exception("<br />No cateory ID Specified!!");
        }
          //  echo "cat id meta: $category_id";
        $category_image=get_category_meta($category_id ,'ns_category_image');
        
        if (!$category_image){
            
             //throw new Exception("<br />Not found cateory<br />");
            return false;
        }else{
            
            return $category_image;
        }
        
    }

}


function get_category_image_url($category_id,$format='original',$father=0){
    
    $category_image=get_category_meta($category_id ,'ns_category_image',$father);
    //echo '<br /> aqui '.  serialize($category_image);

    if ($category_image){
       $url= $category_image->get_image_url($format);
       return $url;
    }else{
        return false;
    }

}
?>