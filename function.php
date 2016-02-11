<?php

class Acf_Bidirectional_Tax{
    var $recursive;
    var $field_key;
    var $taxonomy;
    
    function __construct($taxonomy, $field_key){
        $this->recursive=0;
        $this->setFieldKey($field_key);
        $this->setTaxonomy($taxonomy);
        $this->addActions();
    }
    
    function setFieldKey($val){
        $this->field_key = $val;
    }
    
    function setTaxonomy($val){
        $this->taxonomy = $val;
    }
    
    public function addActions(){
        add_action('acf/save_post', array($this, 'applyBidirectional'), -10); 
    }
    
    public function applyBidirectional($key){
        if(!$this->taxonomy)
            return;
        if(!$this->field_key)
            return;       
        if($this->recursive == 0){
            // variable to avoid recursivity
            $this->recursive = 1;
            $arr=explode("_",$key);
            $tax = $arr[0];
            $id = (int)$arr[1];
            if($tax == $this->taxonomy){
                $rel=get_field($this->field_key,$key);
                if(is_array($rel))                
                    foreach($rel as $term){
                        // check to keep consistency in previously related terms
                        $key_child = $tax."_".$term->term_id;
                        $rel_child=get_field($this->field_key,$key_child);
                        unset($new_rel);
                        foreach($rel_child as $child_elem){
                            if($child_elem->term_id != $id)
                                $new_rel[]=$child_elem->term_id;
                        }
                        update_field($this->field_key, $new_rel, $key_child);
                    } // end check in old relations
            }
            // add relation to new terms related
            if(is_array($_POST["acf"][$this->field_key])){
                foreach($_POST["acf"][$this->field_key] as $termid){
                    $key_child = $tax."_".$termid;
                    $rel_child=get_field($this->field_key,$key_child);
                    unset($new_rel);
                    foreach($rel_child as $child_elem){
                        $new_rel[]=$child_elem->term_id;
                    }
                    if((is_array($new_rel) && !in_array($id, $new_rel)) || empty($new_rel))
                        $new_rel[]=$id;
                    update_field($this->field_key, $new_rel, $key_child);
                }
            }
        }
    }// end method apply_bidirectional
} 

$acf_bidirectional = new Acf_Bidirectional_Tax("category","field_56b9d305d4567");

?>
