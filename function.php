
/* example functions for acf bidirectional relation */
add_action('acf/save_post', 'acf_bidirectional_check_save');
global $acf_recursive;
$acf_recursive = 0;
function acf_bidirectional_check_save($key){
    global $acf_recursive;
    if($acf_recursive == 0){
        // variable to avoid recursivity
        $acf_recursive = 1;
        $arr=explode("_",$key);
        $tax = $arr[0];
        $id = (int)$arr[1];
        // apply to category: choose here your taxonomy
        if($tax == "category"){
            // key of acf relation field
            $field_key = "field_56b9d305d4567";
            
            $rel=get_field($field_key,$key);
            foreach($rel as $term){
                // check to keep consistency in previously related terms
                $key_child = $tax."_".$term->term_id;
                $rel_child=get_field($field_key,$key_child);
                unset($new_rel);
                foreach($rel_child as $child_elem){
                    if($child_elem->term_id != $id)
                        $new_rel[]=$child_elem->term_id;
                }
                update_field($field_key, $new_rel, $key_child);
            } // end check in old relations
            
            // add relation to new terms related
            foreach($_POST["acf"][$field_key] as $termid){
                $key_child = $tax."_".$termid;
                $rel_child=get_field($field_key,$key_child);
                unset($new_rel);
                foreach($rel_child as $child_elem){
                    $new_rel[]=$child_elem->term_id;
                }
                if(!in_array($id, $new_rel) || empty($new_rel))
                    $new_rel[]=$id;
                update_field($field_key, $new_rel, $key_child);
            }
        }
    }
}
