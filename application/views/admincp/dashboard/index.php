<div class="row-fluid">
    <div class="span12">
        <div class="social-box">
            <div class="header">
                <h4><?php echo $template['title']; ?>
            </div>
            <div class="body">
                <input type="text" id="barcode" class="input-block-level" placeholder="扫入条形码">
                <table class="table">
                    <thead>
                        <tr>
                            <th>序号<th>
                            <th><?php echo lang('realname'); ?></th>
                            <th><?php echo lang('community'); ?></th>
                            <th><?php echo lang('signed'); ?></th>
                            <th>序号</th>
                            <th><?php echo lang('realname'); ?></th>
                            <th><?php echo lang('community'); ?></th>
                            <th><?php echo lang('signed'); ?></th>
                            <th>序号</th>
                            <th><?php echo lang('realname'); ?></th>
                            <th><?php echo lang('community'); ?></th>
                            <th><?php echo lang('signed'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
$max = count($list) - 1;
foreach($list as $key=>$item): ?>
<?php if ( $key % 3 == 0 ): ?>
                        <tr>
<?php endif; ?>
                            <td><?php echo $item['ordered_id']; ?></td>
                            <td><?php echo $item['realname']; ?></a></td>
                            <td><?php echo $item['community']; ?></a></td>
                            <td id="id<?php echo $item['id']; ?>"><?php echo $item['signed'] ? '<span class="label label-success" data-toggle="tooltip" title="'.date('Y-m-d H:i:s', $item['signed']).'">'.time_past($item['signed']).'</span>' : '<span class="label">未到</span>'; ?></td>
<?php if ( $key % 3 == 2 || $key == $max ): ?>
                        </tr>
<?php endif; ?>
<?php endforeach; ?>
                    </tbody>
                </table>
        </div>
    </div>
</div>
