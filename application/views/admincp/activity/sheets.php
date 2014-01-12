<div class="social-box">
    <div class="header">
        <h4><?php echo $template['title']; ?></h4>
    </div>
    <div class="body">
        <div class="tabbable tabs-left">
            <ul class="nav nav-tabs nav-tabs-blue">
<?php foreach($sheets as $key=>$value): ?>
                <li><a href="#panel<?php echo $key; ?>" data-url="<?php echo site_url("admincp/activity/sheet/{$aid}/{$file_id}/{$key}"); ?>" data-toggle="tab"><?php echo $value['worksheetName']; ?></a></li>
<?php endforeach; ?>
            </ul>
            <div class="tab-content">
<?php foreach($sheets as $key=>$value): ?>
                <div class="tab-pane" id="panel<?php echo $key; ?>"></div>
<?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
