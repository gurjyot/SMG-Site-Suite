<?php
namespace SMG\WPFoundation\Admin;
final class SettingsRenderer {
    public function render(array $schema,array $values):void{
        foreach($schema as $field){
            if(!is_array($field)||empty($field['key'])||empty($field['type']))continue;
            $key=sanitize_key((string)$field['key']);$type=(string)$field['type'];$label=(string)($field['label']??$key);$description=(string)($field['description']??'');$value=$values[$key]??($field['default']??'');
            echo '<div class="smg-foundation-field">';
            if($type==='checkbox')printf('<label><input type="checkbox" name="settings[%1$s]" value="1" %2$s> <strong>%3$s</strong></label>',esc_attr($key),checked((bool)$value,true,false),esc_html($label));
            else{printf('<label for="smg-field-%1$s"><strong>%2$s</strong></label>',esc_attr($key),esc_html($label));$this->control($type,$key,$value,$field);}
            if($description!=='')echo '<p class="description">'.esc_html($description).'</p>';echo '</div>';
        }
    }
    private function control(string $type,string $key,mixed $value,array $field):void{
        $name='settings['.$key.']';$id='smg-field-'.$key;
        if($type==='media_select'){
            $attachmentId=absint($value);
            $preview=$attachmentId?wp_get_attachment_image($attachmentId,'thumbnail',false,['style'=>'max-width:80px;height:auto;display:block;margin:0 0 8px']):'';
            echo '<div class="smg-foundation-media-field">'.$preview;
            printf('<input type="hidden" id="%1$s" name="%2$s" value="%3$s"><button type="button" class="button smg-foundation-media-select" data-target="%1$s">%4$s</button> <button type="button" class="button-link-delete smg-foundation-media-clear" data-target="%1$s">%5$s</button></div>',esc_attr($id),esc_attr($name),esc_attr((string)$attachmentId),esc_html__('Choose Media','smg-site-suite'),esc_html__('Clear','smg-site-suite'));
        }elseif($type==='page_select'){
            wp_dropdown_pages([
                'name'=>$name,
                'id'=>$id,
                'selected'=>absint($value),
                'show_option_none'=>__('— Select a page —','smg-site-suite'),
                'option_none_value'=>0,
                'post_status'=>'publish',
                'class'=>'regular-text',
            ]);
        }elseif($type==='textarea')printf('<textarea class="large-text" rows="5" id="%1$s" name="%2$s">%3$s</textarea>',esc_attr($id),esc_attr($name),esc_textarea((string)$value));
        elseif($type==='number')printf('<input class="regular-text" type="number" id="%1$s" name="%2$s" value="%3$s">',esc_attr($id),esc_attr($name),esc_attr((string)$value));
        elseif($type==='select'){printf('<select id="%1$s" name="%2$s">',esc_attr($id),esc_attr($name));foreach((array)($field['options']??[]) as $ov=>$ol)printf('<option value="%1$s" %2$s>%3$s</option>',esc_attr((string)$ov),selected((string)$value,(string)$ov,false),esc_html((string)$ol));echo '</select>';}
        elseif($type==='multiselect'){printf('<select multiple size="6" id="%1$s" name="%2$s[]">',esc_attr($id),esc_attr($name));$selectedValues=is_array($value)?array_map('strval',$value):[];foreach((array)($field['options']??[]) as $ov=>$ol)printf('<option value="%1$s" %2$s>%3$s</option>',esc_attr((string)$ov),selected(in_array((string)$ov,$selectedValues,true),true,false),esc_html((string)$ol));echo '</select>';}
        else{$htmlType=in_array($type,['url','email','password'],true)?$type:'text';printf('<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s">',esc_attr($htmlType),esc_attr($id),esc_attr($name),esc_attr((string)$value));}
    }
}
