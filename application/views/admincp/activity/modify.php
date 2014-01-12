<div class="social-box">
    <div class="body">
        <div class="row-fluid">
<?php echo form_open_multipart('', array('class'=>'form-horizontal')); ?>
                <div class="control-group">
                    <label class="control-label" for="name"><?php echo lang('name'); ?></label>
                    <div class="controls">
                        <input type="text" id="name" name="name" value="<?php echo set_value('name', $row['name']); ?>">
                        <?php echo form_error('name'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="location"><?php echo lang('location'); ?></label>
                    <div class="controls">
                        <input type="text" id="location" name="location" value="<?php echo set_value('location', $row['location']); ?>">
                        <?php echo form_error('location'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="start_at"><?php echo lang('start_at'); ?></label>
                    <div class="controls">
                        <input type="datetime" id="start_at" name="start_at" value="<?php echo set_value('start_at', date('Y-m-d H:i', $row['start_at'])); ?>">
                        <?php echo form_error('start_at'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-primary"><?php echo lang('submit'); ?></button>
                    </div>
                </div>
<?php echo form_close(); ?>
        </div>
    </div>
</div>
