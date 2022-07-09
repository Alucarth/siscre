<style>
    #tbl_cities_wrapper td:nth-child(1),
    #tbl_cities_wrapper th:nth-child(1), 
    #tbl_cities_wrapper td:nth-child(2),
    #tbl_cities_wrapper th:nth-child(2) 
    {
        width:40px;
        min-width:40px;
    }
</style>


<div class="row">



    <div class="col-lg-6">

                

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Full name:
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?=$person_info->full_name;?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Email:
            </label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" name="email" value="<?=$person_info->email;?>" />
            </div>
        </div>
        
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Type of Document to Submit
            </label>
            <div class="col-sm-9">
                <?php $id_types = get_type_of_documents(); ?>
                <select class="form-control" id="id_type" name="id_type">
                    <?php foreach ( $id_types as $key => $id_type ): ?>
                        <option value="<?=$key?>" <?=$key==$person_info->id_type?'selected="selected"':''?> ><?=$id_type;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                ID Number
            </label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="id_no" id="id_no" value="<?=$person_info->id_no;?>" />
            </div>
        </div>

        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                Gender
            </label>
            <div class="col-sm-9">
                <select class="form-control">
                    <option value="male" <?=$person_info->gender=='male'?'checked="checked"':'';?>>Male</option>
                    <option value="female" <?=$person_info->gender=='female'?'checked="checked"':'';?>>Female</option>
                </select>
            </div>
        </div>
        
        <div class="form-group row">
            <label class="control-label col-sm-3 text-xs-right">
                <?=$this->lang->line("date of birth")?>:
            </label>
            <div class="col-sm-9">
                <div class="input-group date">
                    <span class="input-group-addon input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"></i></span></span>
                    <input type="text" id="birth_date" name="birth_date" class="form-control" value="<?=$person_info->birth_date != '0000-00-00' ? date($this->config->item('date_format'), strtotime($person_info->birth_date)) : ''?>" />
                </div>
            </div>
        </div>
        
    </div>
    
    
</div>

<script>
    $(document).ready(function(){
        $('.input-group.date').datepicker({
            format: '<?= calendar_date_format(); ?>',
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true
        });
    });
</script>