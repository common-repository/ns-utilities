<?php
require_once (dirname(__FILE__)."/class/ns_category_image.php");
require_once(dirname(__FILE__).'/phpThumb_1.7.9/phpthumb.class.php');

function ns_utilities_catimage(){
    
    //$categories = get_categories( $args );
     $categories = get_categories();
    // echo serialize( $categories );
     
   if(isset( $_REQUEST["acepted"])){
       //echo serialize($_FILES["image"]);
       //$image_path=$_FILES["image"]["tmp_name"].'/'.$_FILES["image"]["name"];
       $image_path=$_FILES["image"]["tmp_name"];
       //echo '<br /> copiar de '.$image_path;
       try{
           //echo 'aqui_ entro ';
           

            $pos_last_point = strrpos($_FILES["image"]["type"],'/');
            $extension = substr($_FILES["image"]["type"], $pos_last_point+1);

            //echo '<br /> *** '.$extension;
           $category_img=ns_category_image::create_category_image($extension,$image_path, $_REQUEST["category_id"]);
           //echo 'aqui_salgo';
       }catch (Exception $ex){
           echo ">> ".$ex;
       }
      //echo '<br />*** '.serialize($category_img);

      $base_path = constant("NS_CAT_IMAGES_BASE_PATH");

       
    }
    //echo '<br /> '.constant("NS_CAT_IMAGES_BASE_PATH");
 

?>

<div class="wrap">

    <h2><?= _e('Assign image Category', 'ns_utilities'); ?></h2>
    <br/>

    <table class="wp-list-table widefat fixed" cellspacing="0">

        <thead>
            <tr>
                <th scope='col' id='id' class='manage-column column-cb check-column'  style="padding:7px 5px;" colspan="0"><?=__("Assign image Category")?></th>
            </tr>
        </thead>

        <tbody id="the-list" class='list:user'>

            <? foreach ($categories as $category){?>
                
                <?
                
                //$cat_img = ns_category_image::get_image_category($category->term_id);
                $image_size=array();
                $image_size["width"]=20;// Esto no lo hace bien me crea una imagen de 20x20 y otra de 13x13
                $image_size["height"]=13;
                $img_url=get_category_image_url($category->term_id,$image_size);
                //$img_url=get_category_image_url($category->term_id,20);
                ?>
                <tr class="alternate">
                    <td >
                        <?//if($cat_img){?>
                        <!--<img id="image_<?//=$category->term_id?>" class="" src="<?//= $cat_img->get_image_url(20)?>" alt="image">-->
                        <?//}else{?>
                           <?//= __("Image");?>
                        <?//}?>
                        <?if($img_url){?>
                            <img id="image_<?=$category->term_id?>" class="" src="<?=$img_url?>" alt="Not image">
                        <?}else{?>
                           <?= __("Not image");?>
                        <?}?>
                        
                        <?
//                        $aux= get_category_image_url($category->term_id,20,true);
//                        if($aux){
//                            echo '<br /> *** '.$aux;
//                        }
//                        else{
//                            echo '<br />^';
//                        }
                        ?>
                    </td>
                    <td >
                        <?=$category->name.' '.$category->term_id?>
                    </td>
                    <td>
                        <div id="search_img_<?=$category->term_id?>"  style="display:none;">
                            <form action="" method="post" enctype="multipart/form-data">
                                <input name="image" type="file" size="10" accept="image/gif" value="-" />
                                <input name="acepted" type="submit" value="acepted" />
                                <input name="category_id" type="hidden" value="<?=$category->term_id?>">
                            </form>
                        </div>
                        <a href="#" onclick="$('search_img_<?=$category->term_id?>').appear(); return false;" id="a_search_img_<?=$category->term_id?>" ><?=__("Upload image")?></a>
                    </td>
                   
                    <td>                      
                        <a href="#" id="delete_img_<?=$category->term_id?>"><?=__('Delete')?></a>
                    </td>
                </tr>
            <?}?>

        </tbody>

        <tfoot>
            <tr>
                <th scope='col' id='id' class='manage-column column-cb check-column'  style="" colspan="0"></th>
            </tr>
        </tfoot>

    </table>

</div>

<script type="text/javascript" language="javascript">
<? foreach ($categories as $category){?>

    <?//$cat_img = ns_category_image::get_image_category($category->term_id);?>
    <?//if($cat_img){?>

    <? $url_img=get_category_image_url($category->term_id,$image_size);?>

    <?if($url_img){?>
        $('delete_img_<?=$category->term_id?>').onclick=function(){

             url='<?= WP_PLUGIN_URL?>/ns_utilities/delete_category_image.php';
             par= 'category_id=<?=$category->term_id?>';
             
             new Ajax.Request(url,{
                   method: 'post',
                   parameters: par,

                   onSuccess:function(resp){
                       //var respJ = eval('(' + resp.responseText + ')');//CUANDO DEVUELVES UNA SERIALIZACION
                       var respJ= resp.responseText; // CUANDO DEVUELVES TRUE O FALSE
                       
                       if(respJ){
                           $("image_<?=$category->term_id?>").src='';
                       }
                   }
             });
             return false;
        }
   
   <? }?>

<?}?>
</script>

<?php }?>